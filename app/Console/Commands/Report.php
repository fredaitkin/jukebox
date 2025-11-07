<?php

/**
 * Report.php
 *
 * @package Jukebox
 * @author  Melissa Aitkin
 */

namespace App\Console\Commands;

use DB;
use Exception;
use Illuminate\Console\Command;
use Mail;

/**
 * Run Jukebox data reports via the command line
 */
class Report extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:report
                            {--type= : Report type}
                            {--query= : Free form report query}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run reports against database';

    /**
     * The report type.
     *
     * @var string
     */
    protected $type;

    /**
     * The report query.
     *
     * @var string
     */
    protected $query;

    /**
     * The report file name.
     *
     * @var string
     */
    protected $filename;

    /**
     * The report title.
     *
     * @var string
     */
    protected $report_title;
    /**
     * Create a new command instance.
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
        $options = $this->options();

        if(isset($options['type'])):
            $this->type = $options['type'];
            $this->runReport();
        endif;

        if(isset($options['query'])):
            $this->query = $options['query'];
            if (! isValidReadQuery($this->query)):
                $this->error('Only read queries are authorized');
                exit;
            endif;
            $this->runDynamicReport();
        endif;

        // Email report
        $this->sendFile();

        // Validate parameters.
        if(empty($this->type) && empty($this->query)):
            $this->error('A report type or report query is required');
        endif;

    }

    /**
     * Run report.
     *
     * @return void
     */
    protected function runReport()
    {
        try {

            switch($this->type):
            case 'countries':
                $query = 'SELECT country, COUNT(*) AS total FROM artists GROUP BY country ORDER BY total DESC, country ASC';
                $records = DB::select($query);
                $this->filename = 'artist_country.csv';
                $this->report_title = "Report of Artists' Countries";
                $this->createCSVReport($records, ['Country', 'Total']);
                break;
            case 'cities':
                $query = "SELECT artist, country, notes FROM artists WHERE notes like '%Location%' ORDER BY country ASC, notes ASC, artist ASC";
                $records = DB::select($query);
                $this->filename = 'artist_city.csv';
                $this->report_title = "Report of Artists' Cities";
                $this->createCSVReport($records, ['Artist', 'Country', 'Location']);
                break;
            default:
                $this->error('This report type does not exist.');
            endswitch;

        } catch (Exception $e) {
            $this->error("{{$e}}");
        }
    }

    /**
     * Run dynamic report.
     *
     * @return void
     */
    protected function runDynamicReport()
    {
        try {

            $records = DB::select($this->query);
            $now = date('YmdHi');
            $this->filename = 'report-' . $now . '.csv';
            $this->report_title = 'Dynamic Report - ' . $now;
            $this->createCSVReport($records);

        } catch (Exception $e) {
            $this->error("{{$e}}");
        }
    }

    /**
     * Create CSV file
     *
     * @param Array $records Report data $paramname
     * @param Array $header  Report header
     *
     * @return void
     */
    protected function createCSVReport(array $records, array $header = null)
    {
        if (isset($records[0])):

            $handle = fopen($this->filename, 'w');

            // Add  report title
            fputcsv($handle, [$this->report_title]);
            fputcsv($handle, []);

            // Add report header
            if (! $header):
                 $header = (array) $records[0];
                 $header = array_keys($header);
            endif;

            fputcsv($handle, $header);

            // Add report data
            foreach($records as $record):
                $record = (array) $record;
                fputcsv($handle, array_values($record));
            endforeach;

            fclose($handle);
            $this->info('The report has been run successfully.');

        else:
            $this->error('No records were returned by this query');
        endif;

    }

    /**
     * Send report via email
     *
     * @return void
     */
    protected function sendFile()
    {
        $data = ['report' => $this->report_title];
        Mail::send(
            'mail_report', $data, function ($message) {
                $message->to(config('mail.report_email'), 'Report Email Address')->subject('MyMusic Report');
                $message->attach(base_path() . DIRECTORY_SEPARATOR . $this->filename);
                $message->from(config('mail.admin_email'), 'MyMusic');
            }
        );
    }

}
