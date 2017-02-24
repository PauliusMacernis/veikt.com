<?php
// TODO: http://php.net/manual/en/function.preg-split.php#92632
namespace App\Console\Commands;

use App\Models\ExpressionHit;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ExpressionHitsPopulateWithWordsFoundInJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expressions:collect';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collects expressions (most of the time - words) mentioned in active Job ads, puts results to expression hits table (hits=0).';

    protected $readByAmount = 1000;
    protected $expressionDelimiters;

    protected $mainSeparator;
    protected $simpleSeparatorsLowercased;
    protected $surroundingPairsOfSeparatorsLowercased;
    protected $regexForSurroundingPairsOfSeparatorsLowercased;
    protected $lowercasedSimpleAndSurroundedSeparatorsWithoutRegex;
    protected $expressionWhiteList;
    protected $expressionBlackList;
    protected $maxExpressionSizeAllowedByVulnerableSqlQuery;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->setMainSeparator();
        $this->setExpressionWhiteList();
        $this->setExpressionBlackList();
        $this->setSimpleSeparatorsLowercased();
        $this->setSurroundingPairsOfSeparatorsLowercased();
        $this->setRegexForSurroundingPairsOfSeparatorsLowercased();
        $this->setLowercasedSimpleAndSurroundedSeparatorsWithoutRegex();
        $this->setMaxExpressionSizeAllowedByVulnerableSqlQuery('expression_hits.expression');

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $isPublishedValue = true;

        $firstActiveJob = (int)\DB::table('job')->select(['id'])->where('is_published', $isPublishedValue)->limit(1)->orderBy('id', 'ASC')->first()->id;
        $lastActiveJob = (int)\DB::table('job')->select(['id'])->where('is_published', $isPublishedValue)->limit(1)->orderBy('id', 'DESC')->first()->id;

        // Just in case something extraordinary happening at the same time we are running this command...
        $lastActiveJob = ($lastActiveJob >= $firstActiveJob) ? $lastActiveJob : $firstActiveJob;
        $maxOffsetInTheory = $lastActiveJob - $firstActiveJob;

        for ($offset = 0; $offset < $maxOffsetInTheory; $offset += $this->readByAmount) {
            /**
             * @var \Illuminate\Support\Collection $activeJobs
             */
            $activeJobs = \DB::table('job')->select(['id', 'content_static_without_tags', 'is_published'])->where('is_published', $isPublishedValue)->offset($offset)->limit($this->readByAmount)->orderBy('id', 'ASC')->get();

            // Real max offset may be reached before $maxOffsetInTheory is reached
            //, because:
            // 1) the process of fetching and analysing lines may take very long
            //    and so first, last or any other record may become unpublicized while the process is running.
            // 2) we may have lots of non-usable IDs between $firstActiveJob and $lastActiveJob
            //
            // For all these reasons, we check if we started to get empty lines. If so, that's the end of analysing.
            // Of course, some lines may be added while we analise too. But we do not care about those, because
            //    new added lines will be analysed with the next run. It is fine for now.
            if ($activeJobs->count() < 1) {
                break;
            }

            $this->saveExpressionsFoundInCollection($activeJobs);

        }
    }

    /**
     * @param Collection $activeJobs
     */
    protected function saveExpressionsFoundInCollection(Collection $jobs)
    {
        foreach ($jobs as $job) {
            $this->saveExpressionsFoundInJob($job);
        }
    }

    protected function saveExpressionsFoundInJob($job)
    {
        $expressions = $this->extractExpressionsFromLowercasedString(mb_strtolower($job->content_static_without_tags));

        // @TODO: Must not be foreach... Must save all at once..
        foreach ($expressions as $expression) {
            $expressionHit = ExpressionHit::firstOrCreate(['expression' => $expression]);
            if (isset($expressionHit->id)) {
                continue;
            }
            $expressionHit->save();
        }

    }

    /**
     * @param $job
     * @return array
     */
    protected function extractExpressionsFromLowercasedString($string)
    {
        //$string = $this->getStringForTesting();

        $expressions = array();
        $expressions['single'] = $this->extractSeparatedByLowercasedSeparators($string);
        $expressions['multi'] = $this->extractSurroundedByPairOfLowercasedSeparators($string); // example: "(" and ")", "[" and "]", etc.

        $expressions = array_merge($expressions['single'], $expressions['multi']);

        //$expressions = $this->trimExpressions($expressions);
        $expressions = $this->removeLongExpressions($expressions, $this->getMaxExpressionSizeAllowedByVulnerableSqlQuery('expression_hits.expression'));
        $expressions = $this->removeBlackListedByUser($expressions); // words, abbreviations, terms, etc. listed by user (data from DB)

        return $expressions;
    }

    protected function extractSeparatedByLowercasedSeparators($string)
    {
        $separators = $this->getLowercasedSimpleAndSurroundedSeparatorsWithoutRegex();

        return explode($separators[0], str_replace($separators, $separators[0], $string));

    }

    /**
     * @return array
     */
    protected function getLowercasedSimpleAndSurroundedSeparatorsWithoutRegex()
    {
        return $this->lowercasedSimpleAndSurroundedSeparatorsWithoutRegex;
    }

    protected function setLowercasedSimpleAndSurroundedSeparatorsWithoutRegex()
    {
        $separators = $this->getSimpleSeparatorsLowercased();

        $surroundedSeparators = $this->getSurroundingPairsOfSeparatorsLowercased();
        $surroundedSeparatorsBeginnings = array_keys($surroundedSeparators);
        $surroundedSeparatorsEndings = array_values($surroundedSeparators);

        $this->lowercasedSimpleAndSurroundedSeparatorsWithoutRegex
            = array_unique(array_merge($separators, $surroundedSeparatorsBeginnings, $surroundedSeparatorsEndings));
    }

    protected function extractSurroundedByPairOfLowercasedSeparators($string)
    {
        $regex = $this->getRegexForSurroundingPairsOfSeparatorsLowercased();

        $results = array();
        preg_match_all($regex, $string, $results);

        return isset($results[4]) ? (array)$results[4] : array();

    }

    /**
     * @return string
     */
    protected function getRegexForSurroundingPairsOfSeparatorsLowercased()
    {
        return $this->regexForSurroundingPairsOfSeparatorsLowercased;
    }

    /**
     * @return string
     */
    protected function setRegexForSurroundingPairsOfSeparatorsLowercased()
    {
        $separators = $this->getSurroundingPairsOfSeparatorsLowercased();

        $regexArray = array_map(function ($key, $value) {
            return "" . preg_quote($key) . "([^" . preg_quote($value) . "]+)" . preg_quote($value) . "";

        }, array_keys($separators), $separators);
        $regex = "/(" . implode(")|(", $regexArray) . ")/iu";

        $this->regexForSurroundingPairsOfSeparatorsLowercased = $regex;

    }

    protected function removeLongExpressions(array $expressions, $maxExpressionSizeAllowed)
    {

        // @TODO: If the user is entering quite long string to search (jobs/opportunities search)
        // field then do something with the long input as it will result to error when writing
        // the query as keyword to db column with small varchar size allowed..

        if (!isset($maxExpressionSizeAllowed)) {
            return $expressions; // No limit set
        }

        foreach ($expressions as $expressionKey => $expression) {
            if (mb_strlen($expression) > $maxExpressionSizeAllowed) {
                unset($expressions[$expressionKey]);
            }
        }

        return $expressions;

    }

    protected function getMaxExpressionSizeAllowedByVulnerableSqlQuery($tableDotColumnString)
    {
        if (
            !isset($this->maxExpressionSizeAllowedByVulnerableSqlQuery)
            || !is_array($this->maxExpressionSizeAllowedByVulnerableSqlQuery)
            || !key_exists($tableDotColumnString, (array)$this->maxExpressionSizeAllowedByVulnerableSqlQuery)
        ) {
            $this->setMaxExpressionSizeAllowedByVulnerableSqlQuery($tableDotColumnString);
        }

        return $this->maxExpressionSizeAllowedByVulnerableSqlQuery[$tableDotColumnString];

    }

    protected function setMaxExpressionSizeAllowedByVulnerableSqlQuery($tableDotColumnString)
    {
        $tableAndColumnArray = explode('.', $tableDotColumnString);
        if (count($tableAndColumnArray) < 2) {
            throw new \Exception("Table and column name string separated by dot was not found.");
        }

        $table = $tableAndColumnArray[0];
        $column = $tableAndColumnArray[1];

        $tableDesc = \DB::select('DESCRIBE ' . $table);

        $columnLowercased = mb_strtolower($column);
        foreach ($tableDesc as $columnFromDec) {
            if (mb_strtolower($columnFromDec->Field) !== $columnLowercased) {
                continue;
            }

            preg_match('/\((.*?)\)/', $columnFromDec->Type, $match);

            $this->maxExpressionSizeAllowedByVulnerableSqlQuery[$tableDotColumnString] = isset($match[1]) ? $match[1] : null;
            return;

        }

        $this->maxExpressionSizeAllowedByVulnerableSqlQuery[$tableDotColumnString] = null;
        return;
    }

    protected function removeBlackListedByUser(array $expressions)
    {
        //return array_diff($this->getExpressionBlackList(), $expressions); // commended, because does not support unicode

        $blackList = $this->getExpressionBlackList();

        foreach ($expressions as $expressionsKey => $expression) {
            foreach ($blackList as $badItem) {
                if (mb_strtolower($expression) === mb_strtolower($badItem)) {
                    unset($expressions[$expressionsKey]);
                }
            }
        }

        return $expressions;

    }

    protected function getExpressionBlackList()
    {
        return $this->expressionBlackList;
    }

    protected function setExpressionBlackList()
    {
        $this->expressionBlackList = array(
            "", // The same as "empty expression is not allowed, because it is not expression..."
        );
    }

    /**
     * @return array
     */
    protected function getLowercasedSimpleAndSurroundedSeparatorsWithRegex()
    {
        $separators = $this->getSimpleSeparatorsLowercased();
        $separatorsRegex = $this->getSimpleSeparatorsRegex();

        $surroundedSeparators = $this->getSurroundingPairsOfSeparatorsLowercased();
        $surroundedSeparatorsBeginnings = array_keys($surroundedSeparators);
        $surroundedSeparatorsEndings = array_values($surroundedSeparators);

        return array_unique(array_merge($separatorsRegex, $separators, $surroundedSeparatorsBeginnings, $surroundedSeparatorsEndings));
    }

    protected function getSimpleSeparatorsLowercased()
    {
        return $this->simpleSeparatorsLowercased;
    }

    protected function setSimpleSeparatorsLowercased()
    {
        $this->simpleSeparatorsLowercased = array(
            $this->getMainSeparator(),
            ".",
            ".",
            "\n", // linefeed (LF or 0x0A (10) in ASCII)
            "\r", // carriage return (CR or 0x0D (13) in ASCII)
            "\t", // horizontal tab (HT or 0x09 (9) in ASCII)
            "\v", // vertical tab (VT or 0x0B (11) in ASCII)
            "\e", // escape (ESC or 0x1B (27) in ASCII)
            "\f", // form feed (FF or 0x0C (12) in ASCII)
            "`",
            "~",
            "!",
            "@",
            "^",
            "*",
            "|",
            "\\",
            ":",
            ";",
            "'",
            "\"",
            ",",
            "/",
            " - ",
            " – ", // U+2013
            "?",
            "»",
            "«",
        );
    }

    protected function getSimpleSeparatorsRegex()
    {
        return array(
            "\\s"
        );
    }

    protected function getSurroundingPairsOfSeparatorsLowercased()
    {
        return $this->surroundingPairsOfSeparatorsLowercased;
    }

    protected function setSurroundingPairsOfSeparatorsLowercased()
    {
        $this->surroundingPairsOfSeparatorsLowercased = array(
            "[" => "]",
            "(" => ")",
            "{" => "}",
            "<" => ">",
            "„" => "“", // U+201E and U+201C
            "“" => "”", // U+... and U+...
        );
    }

    protected function trimExpressions(array $expressions)
    {
        $expressionsSingleByteHexed = []; // @TODO: Should trim separators also (?)
        $trimTheseChars = " \t\n\r\0\x0B" . implode("", $expressionsSingleByteHexed);
        $whiteListedExpressions = $this->getExpressionWhiteList();

        array_walk($expressions, function (&$value) use ($trimTheseChars, $whiteListedExpressions) {

            // Do not trim whitelisted
            foreach ($whiteListedExpressions as $whiteListedExpression) {
                if (mb_strtolower($whiteListedExpression) === mb_strtolower($value)) {
                    return;
                }
            }

            // Trim everything else
            $value = trim($value, $trimTheseChars);

        });

        return $expressions;

    }

    /**
     * @return string
     */
    protected function getExpressionWhiteList()
    {
        return $this->expressionWhiteList;
    }

    protected function setExpressionWhiteList()
    {
        $this->expressionWhiteList = array(
            ' A# ', // https://en.wikipedia.org/wiki/A_Sharp_(.NET)
            ' A+ ', // https://en.wikipedia.org/wiki/A%2B_(programming_language)
            ' B ', // https://en.wikipedia.org/wiki/B_(programming_language)
            ' C ', // https://en.wikipedia.org/wiki/C_(programming_language)
            ' C-- ', // https://en.wikipedia.org/wiki/C--
            ' C++ ', // https://en.wikipedia.org/wiki/C%2B%2B
            ' C# ', // https://en.wikipedia.org/wiki/C_Sharp_(programming_language)
            ' D ', // https://en.wikipedia.org/wiki/D_(programming_language)
            ' E ', // https://en.wikipedia.org/wiki/E_(programming_language)
            ' E# ', // Programming language too
            ' F ', // https://en.wikipedia.org/wiki/F_(programming_language)
            ' F# ', // https://en.wikipedia.org/wiki/F_Sharp_(programming_language)
            ' F* ', // https://en.wikipedia.org/wiki/F*_(programming_language)
            ' G ', // https://en.wikipedia.org/wiki/G_(programming_language)
            ' J ', // https://en.wikipedia.org/wiki/J_(programming_language)
            ' J# ', // https://en.wikipedia.org/wiki/J_Sharp
            ' J++ ', // https://en.wikipedia.org/wiki/Visual_J%2B%2B
            ' K ', // https://en.wikipedia.org/wiki/K_(programming_language)
            ' L ', // https://en.wikipedia.org/wiki/L_(programming_language)
            ' L# ', // https://en.wikipedia.org/wiki/L_Sharp
            ' M2001 ', // https://en.wikipedia.org/wiki/M2001
            ' M4 ', // https://en.wikipedia.org/wiki/M4_(computer_language)
            ' M# ', // https://en.wikipedia.org/wiki/M_Sharp_(programming_language)
            ' o:XML ', // https://en.wikipedia.org/wiki/O:XML
            ' P′′ ', // https://en.wikipedia.org/wiki/P%E2%80%B2%E2%80%B2
            ' P# ', // https://en.wikipedia.org/wiki/P_Sharp
            // ...
        );
    }

    protected function getSimpleAndSurroundedSeparatorsWithoutRegex()
    {
        $separators = $this->getSimpleSeparatorsLowercased();

        $surroundedSeparators = $this->getSurroundingPairsOfSeparatorsLowercased();
        $surroundedSeparatorsBeginnings = array_keys($surroundedSeparators);
        $surroundedSeparatorsEndings = array_values($surroundedSeparators);

        return array_unique(array_merge($separators, $surroundedSeparatorsBeginnings, $surroundedSeparatorsEndings));
    }

    protected function removeArrayItemsIfNotAsciiChar($simpleAndSurroundedSeparatorsWithoutRegex)
    {
        foreach ($simpleAndSurroundedSeparatorsWithoutRegex as $key => $value) {
            if (strlen($value) > 1 || $this->containsAnyMultibyte($value)) {
                unset($simpleAndSurroundedSeparatorsWithoutRegex[$key]);
            }
        }

        return $simpleAndSurroundedSeparatorsWithoutRegex;

    }

    protected function containsAnyMultibyte($string)
    {
        return !mb_check_encoding($string, 'ASCII') && mb_check_encoding($string, 'UTF-8');
    }

    protected function getMainSeparator()
    {
        return $this->mainSeparator;
    }

    protected function setMainSeparator()
    {
        $this->mainSeparator = " ";
    }

    /**
     * @return string
     */
    protected function getStringForTesting()
    {
        $string = "Pardavėja (-as) - konsultantė (-as) (PC Europa, 1 etatas)

 Skelbimas galioja iki: 2017.02.16
 Darbo vieta
 Vilnius
 Darbo pobūdis
 - Klientų konsultavimas apie grožį ir kosmetiką;
- Darbas kasos aparatu;
- Prekių išdėstymas bei jų priežiūra;
- Tvarkos palaikymas prekybos salėje.
 Reikalavimai
 - Mes tikimės, kad Tave nuolat lydi šypsena;
- Domėjimasis grožio ir kosmetikos priemonėmis;
- Panašaus darbo patirtis arba noras jos įgyti;
- Asmeninės savybės - komunikabilumas, darbštumas, sąžiningumas, atsakingumas.
 Mes jums siūlome
 - Įdomų darbą jauname ir draugiškame kolektyve;
- Galimybę pirmam (-ai) sužinoti naujausias grožio srities tendencijas;
- Įdomius ir turiningus mokymus;
- Galimybę lanksčiai derinti darbo grafiką;
- Realias karjeros perspektyvas.
Papildoma informacija:
- Informuosime tik atrinktus kandidatus.
 Siųsti savo CV
 
 « Grįžti į skelbimų sąrašą
 
 
 
 *** ŠIS SKELBIMAS NEATITINKA TIKROVĖS ***
 
 
 
 
 UAB \"Drogas 123\"
 \"Drogas\" – didžiausias kosmetikos ir buities prekių mažmeninės prekybos parduotuvių tinklas Baltijos šalyse ir vienintelis prekių segmento „health &amp; beauty\" (sveikata ir grožis) atstovas regione. Šiuo metu Lietuvoje ir Latvijoje veikia 141 tinklo parduotuvė, iš kurių 53 – Lietuvoje. Nuo 2004 metų birželio „Drogas\" priklauso mažmeninės prekybos ir gamybos grupei „A.S. Watson Group\", kuri yra Honkongo kompanijos „CK Hutchison Holdings Limited\" dalis.
 Visi UAB \"Drogas\" darbo skelbimai
 
 
 
 Kokie žmonės Jus sieja su šia įmone?
 Prisijunkite soc. profiliu ir sužinokite!
 Jungtis C/C++, C#, Object-C, su Facebook »";
        return $string;
    }


}
