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
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Login form page
     * 
     * @param Request $request
     * @return type
     */
    public function index(Request $request)
    {
        return view('default.index');
    }

    /**
     * Display upload form
     * @param Request $request
     * @return type
     */
//    public function upload(Request $request)
//    {
//        return view('default.upload');
//    }

    /**
     * Process uploaded files
     * 
     * @param Request $request
     * @return type
     */
    public function process(Request $request)
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
     * 
     * If 2 files provided:
     * 1. find if strings are different (*) (original|changed)
     * 2. find if string only exists in first file, removed (-) (show old)
     * 3. find if string only exists in second file, added (+) (show new)
     * 4. find if string exists in both files (" ")
     * 
     * If more than 2 files
     * 1. find if string does not exist in first file (+)
     * 2. find if string exists only in first file (-)
     * 3. find if strings are different (*) (original|changed)
     * 
     */
    protected function diff($files)
    {

        $file_data = [];
        $result = [];

        foreach ($files as $k => $file) {

            if (!$opened = $file->openFile("r")) {
                $errors = MessageBag::add(0, sprintf("File %s cannot be read", $file->fileName));
                Redirect::to('index')->withErrors($errors);
            }

            foreach ($opened as $i => $line) {
                $file_data[$k][] = trim(str_replace("/\r\n/", "", $line));
            }
        }

        if (count($files) > 2) {

            $base = $file_data[0];
            unset($file_data[0]);
            sort($file_data);
            $processed = 0;

            foreach ($base as $lid => $line) {
                $result[$lid] = [
                    'line' => $lid + 1,
                    'diff' => null,
                    'value' => $line,
                ];

                foreach ($file_data as $fid => $f) {
                    if (in_array($line, $f)) {
                        $result[$lid]['diff'] = '*';
                        if (!strstr($result[$lid]['value'], $line)) {
                            $result[$lid]['value'] .= "|" . $line;
                        }
                    } else {
                        $result[$lid]['diff'] = '-';
                        $result[$lid]['value'] = $line;
                    }
                }
            }

            $processed = 0;
            foreach ($file_data as $fid => $f) {
                foreach ($f as $lid => $line) {
                    if (in_array($line, $base)) {
                        if (!strstr($result[$lid]['value'], $line)) {
                            $result[$lid]['value'] .= "|" . $line;
                            $result[$lid]['line'] = $lid + 1;
                            $result[$lid]['diff'] = "*";
                        }
                    } else {
                        $result[$lid]['value'] = $line;
                        $result[$lid]['line'] = $lid + 1;
                        $result[$lid]['diff'] = "+";
                    }
                }
            }
        } elseif (count($files) == 2) {

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

            $result_changed[] = ['value' => null, 'diff' => null, 'line' => null];
            if ($changed) {
                foreach ($changed as $k => $v) {
                    $result_changed[] = ['value' => $v, 'diff' => '*', 'line' => $k + 1];
                }
            }

            $result_removed[] = ['value' => null, 'diff' => null, 'line' => null];
            if ($removed) {
                foreach ($removed as $k => $v) {
                    $result_removed[] = ['value' => $v, 'diff' => '-', 'line' => $k + 1];
                }
            }

            $result_both[] = ['value' => null, 'diff' => null, 'line' => null];
            if ($both) {
                foreach ($both as $k => $v) {
                    $result_both[] = ['value' => $v, 'diff' => '', 'line' => $k + 1];
                }
            }

            $result_added[] = ['value' => null, 'diff' => null, 'line' => null];
            if ($added) {
                foreach ($added as $k => $v) {
                    $idx = count($file_data[0]) < count($file_data[1]) ? $k + 2 : $k + 1;
                    $result_added[] = ['value' => $v, 'diff' => '+', 'line' => $idx];
                }
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
