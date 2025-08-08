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
 * Customfield text field plugin strings
 *
 * @package   customfield_dynamic
 * @copyright 2020 Sooraj Singh
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['errordefaultvaluenotinlist'] = 'O valor padrão deve ser uma das opções da lista acima.';
$string['errornotenoughoptions'] = 'Forneça pelo menos duas opções, cada uma em uma nova linha.';
$string['invalidoption'] = 'Opção inválida selecionada';
$string['menuoptions'] = 'Opções de menu (uma por linha)';
$string['pluginname'] = 'Menu suspenso dinâmico';
$string['privacy:metadata'] = 'O plug-in do tipo de campo do menu suspenso dinâmico não armazena nenhum dado pessoal; usa tabelas definidas no core.';
$string['specificsettings'] = 'Configurações dos campos do menu suspenso dinâmico';
$string['enablemultiselect'] = 'Ativar seleção múltipla';
$string['autocomplete'] = 'Ativar modo de preenchimento automático';
$string['autocomplete_help'] = 'Em vez de usar um campo de seleção simples, ative o modo de preenchimento automático';
$string['defaultvalue'] = 'Valor padrão';
$string['defaultvalue_help'] = 'Para seleção múltipla, insira vários valores separados por vírgula, caso contrário, insira um único valor';

$string['queryerrorfalse'] = "Erro ao executar a consulta: o valor de retorno é falso";
$string['queryerrorempty'] = "Nenhum resultado encontrado ao executar a consulta: não foi possível validar";
$string['queryerroridmissing'] = 'falta a coluna id nos valores de retorno da consulta';
$string['queryerrordatamissing'] = 'falta coluna de dados nos valores de retorno da consulta';
$string['queryerrordefaultmissing'] = 'O valor padrão {$a} não existe na lista de valores permitidos';
$string['sqlquery'] = 'Consulta SQL';
$string['sqlerror'] = 'Erro ao executar a consulta';
$string['queryerrormulpliledefault'] = 'Seleção única pode ter um valor padrão, mas {$a} valores encontrados';
