<?php
// TODO: http://php.net/manual/en/function.preg-split.php#92632
namespace App\Console\Commands;

use App\Models\ExpressionHit;
use App\Models\Job;
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


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function getExpressionBlackList()
    {
        return array(

        );
    }

    /**
     * @return string
     */
    protected function getExpressionWhiteList()
    {
        return array(
            'testings'
        );
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
        $lastActiveJob  = (int)\DB::table('job')->select(['id'])->where('is_published', $isPublishedValue)->limit(1)->orderBy('id', 'DESC')->first()->id;

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
            if($activeJobs->count() < 1) {
                break;
            }

            $this->saveExpressionsFoundInCollection($activeJobs);

        }
    }

    /**
     * @param Collection $activeJobs
     */
    protected function saveExpressionsFoundInCollection(Collection $activeJobs)
    {
        foreach ($activeJobs as $job) {
            $this->saveExpressionsFoundInJob($job);
        }
    }

    protected function saveExpressionsFoundInJob($job)
    {

        $expressions = $this->extractExpressionsFromLowercasedString(mb_strtolower($job->content_static_without_tags));

        // @TODO: Must not be foreach... Must save all at once..
        foreach($expressions as $expression) {
            $expressionHit = ExpressionHit::firstOrCreate(['expression' => $expression]);
            if(isset($expressionHit->id)) {
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
        $expressions = array();
        $expressions['single'] = $this->extractSeparatedByLowercasedSeparators($string);
        $expressions['multi'] = $this->extractSurroundedByPairOfLowercasedSeparators($string); // example: "(" and ")", "[" and "]", etc.

        $expressions = array_merge($expressions['single'], $expressions['multi']);

        $expressions = $this->trimExpressions($expressions);
        $expressions = $this->removeLongExpressions($expressions, $this->getMaxExpressionSizeAllowedByVulnerableSqlQuery('expression_hits.expression'));
        $expressions = $this->removeBlackListedByUser($expressions); // words, abbreviations, terms, etc. listed by user (data from DB)

        return $expressions;
    }

    protected function extractSeparatedByLowercasedSeparators($string)
    {
        $separators = $this->getLowercasedSimpleAndSurroundedSeparatorsWithRegex();

        $regex = "/(([" . implode("])|([", $separators) . "]))+/iu";

        $results = preg_split($regex, $string, 0, PREG_SPLIT_NO_EMPTY);

        return (array)$results;
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
        return array(
            ",",
            ".",
            "-",
            "?",
            "»",
            ":",
            ";",
            "\""
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
        return array(
            "[" => "]",
            "(" => ")",
            "{" => "}"
        );
    }

    protected function extractSurroundedByPairOfLowercasedSeparators($string)
    {
        $separators = $this->getSurroundingPairsOfSeparatorsLowercased();

        $regexArray = array_map(function ($key, $value) {
            return "" . preg_quote($key) . "([^" . preg_quote($value) . "]+)" . preg_quote($value) . "";

        }, array_keys($separators), $separators);
        $regex = "/(" . implode(")|(", $regexArray) . ")/iu";

        $results = array();
        preg_match_all($regex, $string, $results);

        return isset($results[4]) ? (array)$results[4] : array();

    }

    protected function trimExpressions(array $expressions)
    {

//        $expressionsAnyByte     = $this->getSimpleAndSurroundedSeparatorsWithoutRegex();
//        $expressionsSingleByte  = $this->removeArrayItemsIfNotAsciiChar($expressionsAnyByte);
//
//        $expressionsSingleByteHexed = array_map(function($expressionSingleByte) {
//            return '\\x' . bin2hex($expressionSingleByte);
//        }, $expressionsSingleByte);


        $expressionsSingleByteHexed = []; // @TODO: Should trim separators also (?)
        $trimTheseChars = " \t\n\r\0\x0B" . implode("", $expressionsSingleByteHexed);
        $whiteListedExpressions = $this->getExpressionWhiteList();

        array_walk($expressions, function (&$value) use ($trimTheseChars, $whiteListedExpressions) {
            foreach ($whiteListedExpressions as $whiteListedExpression) {
                if (mb_strtolower($whiteListedExpression) === mb_strtolower($value)) {
                    return;
                }
            }

            $value = trim($value, $trimTheseChars);

        });

        return $expressions;

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

    protected function getMaxExpressionSizeAllowedByVulnerableSqlQuery($tableDotColumnString)
    {
        $tableAndColumnArray = explode('.', $tableDotColumnString);
        if(count($tableAndColumnArray) < 2) {
            throw new \Exception("Table and column name string separated by dot was not found.");
        }

        $table = $tableAndColumnArray[0];
        $column = $tableAndColumnArray[1];

        $tableDesc = \DB::select('DESCRIBE ' . $table);

        $columnLowercased = mb_strtolower($column);
        foreach($tableDesc as $columnFromDec) {
            if(mb_strtolower($columnFromDec->Field) !== $columnLowercased) {
                continue;
            }

            preg_match('/\((.*?)\)/', $columnFromDec->Type, $match);

            return isset($match[1]) ? $match[1] : null;

        }

        return null;

    }

    protected function removeLongExpressions(array $expressions, $maxExpressionSizeAllowed)
    {

        // @TODO: If the user is entering quite long string to search (jobs/opportunities search)
        // field then do something with the long input as it will result to error when writing
        // the query as keyword to db column with small varchar size allowed..

        if(!isset($maxExpressionSizeAllowed)) {
            return $expressions; // No limit set
        }

        foreach($expressions as $expressionKey => $expression) {
            if(mb_strlen($expression) > $maxExpressionSizeAllowed) {
                unset($expressions[$expressionKey]);
            }
        }

        return $expressions;

    }


}
