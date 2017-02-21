<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Models\JobExpression;
use Illuminate\Console\Command;

class ExpressionHitsUpdateAmountsinJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expressions:count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks every job for every keyword. It is massive thing..';

    protected $readJobsByAmount = 1000;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
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
        $countActiveJobs = Job::where('is_published', $isPublishedValue)->count();

        //\DB::enableQueryLog();
        for ($i = 0; $i <= $countActiveJobs; $i += $this->readJobsByAmount) {
            /**
             * @var \Illuminate\Support\Collection $activeJobs
             */
            $activeJobs = \DB::table('job')->select(['id', 'content_static_without_tags', 'is_published'])->where('is_published', $isPublishedValue)->offset($i)->limit($this->readJobsByAmount)->get();

            $this->countAndSave($activeJobs);
        }
    }

    protected function countAndSave($jobs)
    {
        if(!$jobs->count()) {
            return; // Nothing to do, jo jobs given
        }

        JobExpression::query()->truncate();
        foreach($jobs as $job) {
            $this->countAndSaveExpressionsFound($job->id, $job->content_static_without_tags);
        }
    }

    protected function countAndSaveExpressionsFound($jobId, $jobTextToSearchIn)
    {
        $expressions = $this->getExpressionsAppliedToJob($jobId);
        $expressionsCounted = $this->countExpressionsInText($jobTextToSearchIn, $expressions);
        unset($expressions);

        foreach($expressionsCounted as $expression) {
            $jobExpression = JobExpression::firstOrCreate([
                'job_id'        => $jobId,
                'expression_id' => $expression->expression_id
            ]);
            $jobExpression->expressions_found = (int)$expression->count;
            $jobExpression->save();
        }
        unset($expressionsCounted);
    }

    protected function getExpressionsAppliedToJob($jobId)
    {
        $expressions = \DB::select(
           'SELECT
                j.id as job_id, e.id as expression_id, e.expression
            FROM job as j
            JOIN expression_hits as e ON (j.content_static_without_tags LIKE CONCAT(\'%\', e.expression, \'%\'))
            
            WHERE j.id = ?', [$jobId]);

        return $expressions;
    }

    protected function countExpressionsInText($jobTextToSearchIn, $expressions)
    {
        foreach($expressions as $key => &$expression) {
            $count = mb_substr_count($jobTextToSearchIn, (string)$expression->expression);
            if(!$count) {
                // @TODO: There should not be any zeros in here!
                unset($expressions[$key]);
            } else {
                $expression->count = $count;
            }
        }

        return $expressions;

    }
}
