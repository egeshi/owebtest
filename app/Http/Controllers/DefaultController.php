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

    /**
     * Constructor
     * Enable Authorization for all pages
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Redirect to upload page
     *
     * @param  Request $request
     * @return type
     */
    public function index(Request $request)
    {
        return Redirect::to('/upload');
    }

    /**
     * Diff uploaded files
     *
     * 1. find if strings are different (*) (original|changed)
     * 2. find if string only exists in first file, removed (-) (show old)
     * 3. find if string only exists in second file, added (+) (show new)
     * 4. find if string exists in both files (" ")
     *
     */
    public function process(Request $request)
    {

        $files = Input::file('files');
        $uploaded = 0;

        foreach ($files as $file) {
            $req = array(
                'file' => 'required|mimes:text');
            $validator = Validator::make(array('file' => $file), $req);

            if ($validator->passes()) {
                $dest = 'uploaded';
                $filename = $file->getClientOriginalName();
                $saved[] = $file->move($dest, $filename);
                $uploaded++;
            } else {
                //return Redirect::to('index')->withInput()->withErrors($validator);
                return view(
                    'errors.validation',
                    [
                    'errors' => $validator->errors()
                    ]
                );
            }
        }

        if ($uploaded == count($files)) {

            $file_data = [];
            $result = [];

            foreach ($saved as $k => $file) {

                if (!$opened = $file->openFile("r")) {
                    $errors = MessageBag::add(0, sprintf("File %s cannot be read", $file->fileName));
                    Redirect::to('index')->withErrors($errors);
                }

                foreach ($opened as $i => $line) {
                    $file_data[$k][] = trim(str_replace("/\r\n/", "", $line));
                }
            }

            if (count($file_data) > 2) {

                $base = $file_data[0];
                array_shift($file_data);

                do {
                    $result[] = $this->diff($base, $file_data[0]);
                    array_shift($file_data);
                } while (count($file_data));
            } else {
                $result = $this->diff($file_data[0], $file_data[1]);
            }
        }

        return view(
            'default.result',
            [
            'result' => $result,
            'multi' => ((count($files) > 2) ? true : false)
            ]
        );
    }

    /**
     * Detect differences
     *
     * @param array $base
     * @param array $comp
     * @return array
     */
    protected function diff($base, $comp)
    {

        $both = array_intersect($base, $comp);
        $removed = array_diff($base, $comp);
        $added = array_diff($comp, $base);

        $na = array_intersect_key($base, $comp);
        foreach ($na as $k => $v) {
            if ($comp[$k] !== $v && !in_array($v, $both)) {

                $changed[$k] = $v . "|" . $comp[$k];

                if (array_key_exists($k, $added)) {
                    unset($added[$k]);
                    unset($removed[$k]);
                } elseif (array_key_exists($k, $removed)) {
                    unset($changed[$k]);
                }
            }
        }

        if (count($changed)) {
            foreach ($changed as $k => $v) {
                $result_changed[] = ['value' => $v, 'diff' => '*', 'line' => $k + 1];
            }
        }

        if (count($removed)) {
            foreach ($removed as $k => $v) {
                $result_removed[] = ['value' => $v, 'diff' => '-', 'line' => $k + 1];
            }
        }

        if (count($both)) {
            foreach ($both as $k => $v) {
                $result_both[] = ['value' => $v, 'diff' => '', 'line' => $k + 1];
            }
        }

        if (count($added)) {
            foreach ($added as $k => $v) {
                $idx = count($base) < count($comp) ? $k + 2 : $k + 1;
                $result_added[] = ['value' => $v, 'diff' => '+', 'line' => $idx];
            }
        }

        $result = array_merge(
            (isset($result_changed) ? $result_changed : array()),
            (isset($result_removed) ? $result_removed : array()),
            (isset($result_both) ? $result_both : array()),
            (isset($result_added) ? $result_added : array())
        );

        $line = [];
        foreach ($result as $k => $row) {
            $line[] = $row['line'];
        }

        array_multisort($line, SORT_ASC, $result);

        return $result;
    }
}
