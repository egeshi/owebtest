<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Validator;
use Redirect;
use MessageBag;

class DefaultController extends Controller
{

    public function index(Request $request)
    {
        return view('default.index');
    }

    public function upload(Request $request)
    {
        return view('default.upload');
    }

    public function process(Request $request)
    {

        $files = Input::file('files');
        $uploaded = 0;

        //var_dump($files);
        //die(__FILE__ . ":" . __LINE__);

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
            $diff = $this->diff($saved);
            Redirect::to('index');
        } else {
            return Redirect::to('index')->withInput()->withErrors($validator);
        }

        return view('default.result', [
            'result' => $diff
        ]);
    }

    /**
     * Compare files
     */
    protected function diff($files)
    {

        $file_data = [];
        $result = [];

        if (count($files) > 2) {

            foreach ($files as $file) {

                if (!$opened = $file->openFile("r")) {
                    $errors = MessageBag::add(0, sprintf("File %s cannot be read", $file->fileName));
                    Redirect::to('index')->withErrors($errors);
                }

                foreach ($opened as $k => $line) {
                    $value = trim(str_replace("/\r\n/", "", $line));
                    if ($value) {
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
                            $result[$k]['value'] .= "|" . $c['lines'][$k];
                            $result[$k]['diff'] = '*';
                        }
                    } else {
                        $result[$k]['value'] = $cmp['lines'][$k]; //string only exists in first array
                        $result[$k]['diff'] = '-';
                    }
                }
            }
        } elseif (count($files) == 2) {
            //1. find if strings are different (*) (original|changed)
            //2. find if string only exists in first file, removed (-) (show old)
            //3. find if string only exists in second file, added (+) (show new)
            //4. find if string exists in both files (" ")

            foreach ($files as $k => $file) {

                if (!$opened = $file->openFile("r")) {
                    $errors = MessageBag::add(0, sprintf("File %s cannot be read", $file->fileName));
                    Redirect::to('index')->withErrors($errors);
                }

                foreach ($opened as $i => $line) {
                    $file_data[$k][] = trim(str_replace("/\r\n/", "", $line));
                }
            }

            $both = array_intersect($file_data[0], $file_data[1]);
            $removed = array_diff($file_data[0], $file_data[1]);
            $added = array_diff($file_data[1], $file_data[0]);

            $possiblyChanged = array_intersect_key($file_data[0], $file_data[1]);
            foreach ($possiblyChanged as $k => $v) {
                if ($file_data[1][$k] !== $v && !in_array($v, $both)) {

                    $changed[$k] = $v . "|" . $file_data[1][$k];

                    if (array_key_exists($k, $added)) {
                        unset($added[$k]);
                        unset($removed[$k]);
                    } elseif (array_key_exists($k, $removed)) {
                        unset($changed[$k]);
                    }
                }
            }

            foreach ($changed as $k => $v) {
                $result_changed[] = ['value' => $v, 'diff' => '*', 'line' => $k + 1];
            }
            
            foreach ($removed as $k => $v) {
                $result_removed[] = ['value' => $v, 'diff' => '-', 'line' => $k + 1];
            }

            foreach ($both as $k => $v) {
                $result_both[] = ['value' => $v, 'diff' => '', 'line' => $k + 1];
            }

            foreach ($added as $k => $v) {
                $idx = count($file_data[0])<count($file_data[1]) ? $k+2 : $k+1;
                $result_added[] = ['value' => $v, 'diff' => '+', 'line' => $idx];
            }

            $result = array_merge($result_changed, $result_removed, $result_both, $result_added);

            $line = [];
            foreach ($result as $k => $row) {
                $line[] = $row['line'];
            }

            array_multisort($line, SORT_ASC, $result);

        }

        return $result;
    }

}
