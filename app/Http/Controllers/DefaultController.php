<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Validator;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\MessageBag;
use Symfony\Component\HttpFoundation\File\File as File;

class DefaultController extends Controller
{

    public function index(Request $request)
    {
        return view('default.index');
    }

    public function upload(Request $request)
    {

        $files = Input::file('files');
        $uploaded = 0;

        foreach ($files as $file) {
            $req = array('file' => 'required');
            $validator = Validator::make(array('file' => $file), $req);

            if ($validator->passes()) {
                $dest = 'uploaded';
                $filename = $file->getClientOriginalName();
                $saved[] = $file->move($dest, $filename);
                $uploaded++;
            }
        }

        if ($uploaded == count($files)) {
            Session::flash('success', 'All files uploaded!');
            $diff = $this->diff($saved);
            Redirect::to('index');
        } else {
            return Redirect::to('index')->withInput()->withErrors($validator);
        }

        return view('default.result', [
            'result'=>$diff
        ]);
    }

    /**
     * Compare files
     */
    protected function diff($files)
    {

        $file_data = [];
        
        if (count($files) > 2) {
            foreach ($files as $file) {

                if (!$opened = $file->openFile("r")) {
                    $errors = MessageBag::add(0, sprintf("File %s cannot be read", $file->fileName));
                    Redirect::to('index')->withErrors($errors);
                }

                foreach ($opened as $line) {
                    $value = trim(str_replace("/\r\n/", "", $line));
                    if ($value){
                        $file_data[$opened->getFilename()]['lines'][] = $value;
                    }
                }
            }

            $largest = '';
            $longest = 0;
            $cmp = [];
            foreach ($file_data as $k => $f) {
                end($file_data[$k]['lines']);
                $length = key($file_data[$k]['lines']);
                $file_data[$k]['length'] = $length;
                if ($length > $longest) {
                    $longest = $length;
                    $largest = $k;
                    $cmp = $file_data[$k];
                    unset($file_data[$k]);
                }
            }

            $result = [];

            //1. find if string is different (*) (original|changed)
            //2. find if string only exists in first file (-) (original)
            //3. find if string exists in all files (" ")

            foreach ($cmp['lines'] as $k => $l) {
                $result[$k] = [
                    'value' => $cmp['lines'][$k],
                    'diff' => null,
                    'line' => $k,
                ];
                foreach ($file_data as $c) {
                    if (array_key_exists($k, $c['lines'])) {
                        if (strcmp($cmp['lines'][$k], $c['lines'][$k]) == 0) {
                            $result[$k]['value'] = $cmp['lines'][$k]; //strings are equal
                            $result[$k]['diff'] = '';
                        } elseif (strcmp($cmp['lines'][$k], $c['lines'][$k]) != 0) {
                            $result[$k]['value'] .= "|".$c['lines'][$k];
                            $result[$k]['diff'] = '*';
                        }
                    } else {
                        $result[$k]['value'] = $cmp['lines'][$k]; //string only exists in first array
                        $result[$k]['diff'] = '-';
                    }
                }
            }
        } elseif (count($files) == 2) {
            //1. find if string is different (*) (original|changed)
            //2. find if string only exists in first file (-) (original)
            //3. find if string exists NOT in first file (+) (different)
            //4. find if string exists in both files (" ")
            
            die(__FILE__ . ":" . __LINE__);
        } else {
            die(__FILE__ . ":" . __LINE__);
        }
        
        //var_dump($result);
        //die(__FILE__ . ":" . __LINE__);
        
        return $result;
        
    }

}
