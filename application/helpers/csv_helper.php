<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Csv_helper
{
    public static function get_csv($items, $preferences, $file_name = 'output.csv', $fields_price = array(), $fields_kg = array())
    {
        $CI =& get_instance();
        // Initiate Csv File
        header('Content-Type: text/csv; charset = utf-8');
        header('Content-Disposition: attachment; filename = ' . $file_name);
        $handle = fopen('php://output', 'w');

        $preference_headers = array();
        $row = array();
        foreach ($preferences as $id => $value)
        {
            if ($value)
            {
                $preference_headers[] = $id;
                $row[] = $CI->lang->line('LABEL_' . strtoupper($id));
            }
        }
        fputcsv($handle, $row); // Column headers

        foreach ($items as $item)
        {
            $row = array();
            foreach ($preference_headers as $preference_head)
            {
                if ((sizeof($fields_price) > 0) && in_array($preference_head, $fields_price))
                {
                    $row[] = System_helper::get_string_amount($item[$preference_head]);
                }
                else if ((sizeof($fields_kg) > 0) && in_array($preference_head, $fields_kg))
                {
                    $row[] = System_helper::get_string_kg($item[$preference_head]);
                }
                else
                {
                    $row[] = $item[$preference_head];
                }
            }
            fputcsv($handle, $row); // Records
        }

        fclose($handle);
    }
}
