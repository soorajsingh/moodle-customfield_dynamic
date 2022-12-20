<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Class field
 *
 * @package   customfield_dynamic
 * @copyright 2020 Sooraj Singh
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace customfield_dynamic;

defined('MOODLE_INTERNAL') || die;

/**
 * Class field
 *
 * @package customfield_dynamic
 * @copyright 2020 Sooraj Singh
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class field_controller extends \core_customfield\field_controller {
    /**
     * Customfield type
     */
    const TYPE = 'dynamic';

    /**
     * Add fields for editing a dynamic field.
     *
     * @param \MoodleQuickForm $mform
     */
    public function config_form_definition(\MoodleQuickForm $mform) {
        $mform->addElement('header', 'header_specificsettings', get_string('specificsettings', 'customfield_dynamic'));
        $mform->setExpanded('header_specificsettings', true);

        $mform->addElement('textarea', 'configdata[dynamicsql]', get_string('sqlquery', 'customfield_dynamic'),
         array('rows' => 7, 'cols' => 52));
        $mform->setType('configdata[dynamicsql]', PARAM_RAW);

        $mform->addElement('advcheckbox', 'configdata[autocomplete]', get_string('autocomplete', 'customfield_dynamic'),
         '', array('group' => 1), array(0, 1));
        $mform->addHelpButton('configdata[autocomplete]', 'autocomplete', 'customfield_dynamic');

        $mform->addElement('text', 'configdata[defaultvalue]', get_string('defaultvalue', 'core_customfield'), 'size="50"');
        $mform->setType('configdata[defaultvalue]', PARAM_RAW);

        $mform->addHelpButton('configdata[defaultvalue]', 'defaultvalue', 'customfield_dynamic');

        $mform->addElement('advcheckbox', 'configdata[multiselect]', get_string('enablemultiselect', 'customfield_dynamic'),
         '', array('group' => 1), array(0, 1));
    }

    /**
     * Returns the options available as an array.
     *
     * @param \core_customfield\field_controller $field
     * @return array
     */
    public static function get_options_array(\core_customfield\field_controller $field,$multiselect=0) : array {
        global $DB;
        if ($field->get_configdata_property('dynamicsql')) {
            $resultset = $DB->get_records_sql($field->get_configdata_property('dynamicsql'));
            $options = array();
            foreach ($resultset as $key => $option) {
                $options[format_string($key)] = format_string($option->data);// Multilang formatting.
            }
        } else {
            $options = array();
        }
        if($multiselect){
            return $options;
        }
        else{
            return array('' => get_string('choose')) + $options;
        }

    }

    /**
     * Validate the data from the config form.
     * Sub classes must reimplement it.
     *
     * @param array $data from the add/edit profile field form
     * @param array $files
     * @return array associative array of error messages
     */
    public function config_form_validation(array $data, $files = array()) : array {
        global $DB;
        $err = array();
        try {
            $sql = $data['configdata']['dynamicsql'];
            if (!isset($sql) || $sql == '') {
                $err['configdata[dynamicsql]'] = get_string('err_required', 'form');
            } else {
                $resultset = $DB->get_records_sql($sql);
                if (!$resultset) {
                    $err['configdata[dynamicsql]'] = get_string('queryerrorfalse', 'customfield_dynamic');
                } else {
                    if (count($resultset) == 0) {
                        $err['configdata[dynamicsql]'] = get_string('queryerrorempty', 'customfield_dynamic');
                    } else {
                        $firstval = reset($resultset);
                        if (!object_property_exists($firstval, 'id')) {
                            $err['configdata[dynamicsql]'] = get_string('queryerroridmissing', 'customfield_dynamic');
                        } else {
                            if (!object_property_exists($firstval, 'data')) {
                                $err['configdata[dynamicsql]'] = get_string('queryerrordatamissing', 'customfield_dynamic');
                            } else if (!empty($data['configdata']['defaultvalue'])) {
                                // Def missing.
                                $defaultvalue = $data['configdata']['defaultvalue'];
                                $options = array_column($resultset, 'data', 'id');
                                $values = explode(',', $defaultvalue);

                                if ($data['configdata']['multiselect'] == 0 && count($values) > 1) {
                                    $err['configdata[defaultvalue]'] = get_string('queryerrormulipledefault',
                                     'customfield_dynamic', count($values));
                                } else if ($data['configdata']['multiselect'] == 0 && !array_key_exists($defaultvalue, $options)) {
                                    $err['configdata[defaultvalue]'] = get_string('queryerrordefaultmissing',
                                     'customfield_dynamic', $defaultvalue);
                                } else {
                                    foreach ($values as $val) {
                                        if (!array_key_exists($val, $options)) {
                                            $err['configdata[defaultvalue]'] = get_string('queryerrordefaultmissing',
                                             'customfield_dynamic', $val);
                                            break;
                                        }
                                    }
                                }

                            }
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            $err['configdata[dynamicsql]'] = get_string('sqlerror', 'customfield_dynamic') . ': ' .$e->getMessage();
        }
        return $err;
    }
}
