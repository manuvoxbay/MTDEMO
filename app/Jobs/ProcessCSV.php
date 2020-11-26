<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Config;
use App\Excel;
use App\Jobs\SendMail;

class ProcessCSV implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $input;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->input                                    = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
       try
       {
            $path                                       = $this->input;
            $empty_error                                = [];
            $invalid_error                              = [];
            $syntax_error                               = [];
            $f                                          = 0;
            
            $handle                                     = fopen($path,"r");
            if(!$handle)
            {
                return response()->json([
                    'status'                            => false,
                    'message'                           => 'Unable to open the input file'
                ],400);
            }
            else
            {
                $col_count                              = Config::get('common_config.col_count');
                $col_name                               = Config::get('common_config.col_name');
                $err_header2                            ='';
                $err_header                             ='';

                $processed                              = 0;
                $i                                      = 0;
                $errors                                 = [];
                $inputData                              = [];
                while(($data                            = fgetcsv($handle,1000,',')))
                {
                    if($i                               == 0)
                    {
                        if($col_count                   != count($data))
                        {
                            $f                          = 1;
                            $errors['header_1']         = "column count and csv header count is missmatched";
                        }
                        else
                        {
                            $result                     = array_diff($data,$col_name);
                            if(!empty($result))
                            {
                                $f                      = 1;
                                $errors['header_2']     = 'header columns('.implode($result,',').') is incorrect in csv';
                            }
                        }
                    }
                    else
                    {
                        //processing the data
                        $m                              = 0;
                        while($m < $col_count)
                        {
                            $index                      = $col_name[$m];

                            if(empty($data[$m]))
                            { 
                                $index                  .= "_empty";
                                if(empty($errors[$index]))
                                {
                                    $errors[$index]     = $col_name[$m].' is empty on ';
                                }
                                else
                                {
                                    $errors[$index]     = $errors[$index].', ';
                                }
                                $errors[$index]         = $errors[$index].$i;
                                $f                      = 1;
                            }
                            else
                            {
                                if(!$this->validateField($data[$m],$m))
                                {
                                    $index              .= "_invalid";
                                    if(empty($errors[$index]))
                                    {
                                        $errors[$index] = $col_name[$m].' is invalid on ';
                                    }
                                    else
                                    {
                                        $errors[$index] = $errors[$index].', ';
                                    }
                                    $errors[$index]     = $errors[$index].$i;
                                    $f                  = 1;
                                }
                            }
                            $m++;
                        }
                        if(!$f)
                        {
                            $inputData[]                = $data;
                            $excel                      = new Excel();
                            $excel->module_code         = $data[0];
                            $excel->module_name         = $data[1];
                            $excel->module_term         = $data[2];
                            $excel->save();
                            $processed++;
                        }
                    }
                    $f                                  = 0;
                    $i++;
                }
            }

            $mailData['status']                         = 1;
            $mailData['processed']                      = $processed;
            $mailData['data']                           = $errors;
            
            $this->sendMailer($mailData);
            return true;
       }
       catch(\Exception $e)
       {
            $mailData['status']                         = 2;
            $mailData['data']                           = $e->getMessage();
            $this->sendMailer($mailData);
       }
    }
    public function sendMailer($data=[])
    {
        $mailData                                       = [
            'template'                                  => 'emails.excel_result',
            'email'                                     => Config::get('common_config.report_to'),
            'data'                                      => [],
            'subject'                                   => 'CSV file execution completed'
        ];
        $mailData                                       = array_merge($mailData, $data);
        SendMail::dispatch($mailData);
    }

    public function validateField($value,$m)
    {
        try
        {
            $rules                                      = Config::get('common_config.rules');
            if(empty($rules[$m]))
            {
                return true;
            }
            $pattern                                    = $rules[$m];
            
            if(preg_match($pattern, $value))
            {
                return true;
            }
        }
        catch(\Exception $e)
        {
            //
        }
        return false;
    }
}
