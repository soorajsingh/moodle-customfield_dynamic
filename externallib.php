<?php
// externalib.php
namespace customfield_dynamic\external;

use customfield_dynamic\field_controller;
use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/externallib.php');

class get_dynamic_options extends \external_api {
    public static function get_options_parameters() {
        return new external_function_parameters([
            'instance' => new external_value(PARAM_INT, 'Instancia'),
            'search' => new external_value(PARAM_RAW, 'Termo de busca', VALUE_DEFAULT,null),
        ]);
    }

    public static function get_options($instance,$search) {
        global $COURSE;


        $field = \core_customfield\field_controller::create($instance);
        // Obter o contexto do curso
        $context = $field->get_handler()->get_configuration_context();
        $options = field_controller::get_options_array($field,$field->get_configdata_property('multiselect'));
        // Filtrar os templates com base no termo de busca
        $count = 0;
        $result = [];
        foreach ($options as $key => $option) {
            // Multilang formatting with filters.
            $name = format_string($option, true, ['context' => $context]);
            if (stripos($name, $search) !== false) {
                $result[] = [
                    'value' => $key,
                    'label' => $name,
                ];
                $count++;
                if ($count >= 10) {
                    break;
                }
            }
        }


        return $result;
    }

    public static function get_options_returns() {
        return new \external_multiple_structure(
            new \external_single_structure([
                'label' => new \external_value(PARAM_TEXT, 'Option label'),
                'value' => new \external_value(PARAM_RAW, 'Option value'),
            ])
        );
    }
}
