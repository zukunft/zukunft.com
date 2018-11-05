<?php

/*

  xml.php - to im- and export xml files
  -------
  
  offer the user the long or the short version
  the short version is using one time ids for words, triples and groups
  
  add the instance id, user id and time stamp to the export file
  
  
  This file is part of zukunft.com - calc with words

  zukunft.com is free software: you can redistribute it and/or modify it
  under the terms of the GNU General Public License as
  published by the Free Software Foundation, either version 3 of
  the License, or (at your option) any later version.
  zukunft.com is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.
  
  You should have received a copy of the GNU General Public License
  along with zukunft.com. If not, see <http://www.gnu.org/licenses/gpl.html>.
  
  To contact the authors write to:
  Timon Zielonka <timon@zukunft.com>
  
  Copyright (c) 1995-2018 zukunft.com AG, Zurich
  Heang Lor <heang@zukunft.com>
  
  http://zukunft.com
  
*/

class xml_export {

  // parameters to filter the export
  public $usr     = NULL; // the user who wants to im- or export
  public $phr_lst = NULL; // to export all values related to this phrase
  
  // to build the xml
  //public $phr_lst_used = NULL; // all phrases used by the exported values
  
  // export zukunft.com data as xml
  function export ($debug) {

    // 0. init text vars for export
    $result = '';
    $phr_lst_used      = New phrase_list;
    $phr_lst_used->usr = $this->usr;
    
    // 1. create the xml with a header
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><ZUKUNFT.COM_DATA_XML/>');
    $xml_cfg = $xml->addChild('EXPORT_HEADER');
    $xml_cfg->addChild('VERSION', PRG_VERSION);
    $xml_cfg->addChild('POD', cfg_get(CFG_SITE_NAME, $this->usr, $debug-1));
    $xml_cfg->addChild('TIME', date("Y-m-d H:i:s"));
    $xml_cfg->addChild('USER', $this->usr->name);
    $xml_cfg->addChild('PHRASE', $this->phr_lst->name($debug-1));

    
    // 2. collect and export the values

    // get all words and triples needed for the values that should be exported
    zu_debug("xml_file->export ... load.", $debug-10);
    $val_lst = New value_list;
    $val_lst->usr     = $this->usr;
    $val_lst->phr_lst = $this->phr_lst;
    $val_lst->load_all($debug-1);

    foreach ($val_lst->lst AS $val) {
      $val->load($debug-1);
      $val->load_phrases($debug-1);
      $xml_val = $xml->addChild('VALUE');
      foreach ($val->wrd_lst->lst AS $wrd) {
        $xml_val->addChild('WORD', $wrd->name());
        zu_debug('xml_file->export ... word '.$wrd->name().' done.', $debug-18);
      }
      foreach ($val->lnk_lst->lst AS $lnk) {
        zu_debug('xml_file->export ... triple .', $debug-10);
        $xml_val->addChild('TRIPLE', $lnk->name());
        zu_debug('xml_file->export ... triple '.$lnk->name().'.', $debug-18);
      }
      if (isset($val->time_phr)) {
        //$xml_val->addChild('time_word', $val->time_id); 
        $phr = New phrase;
        $phr->usr = $this->usr;
        $phr->id  = $val->time_id;
        $phr->load($debug-1);
        $xml_val->addChild('TIME', $phr->name); 
      }
      $xml_val->addChild('NUMBER', $val->number);
      
      // 3. add all used words to the list to export
      $wrd_lst_all = $val->phr_lst->wrd_lst_all($debug-1);
      foreach ($wrd_lst_all->lst AS $wrd) {
        if (!array_key_exists($wrd->id, $phr_lst_used->ids)) {
          $phr_lst_used->add($wrd, $debug-1);
        }
      }
    }
    
    // 4. export all word relations
    $lnk_lst = New word_link_list;
    $lnk_lst->usr       = $this->usr;
    $lnk_lst->wrd_lst   = $phr_lst_used;
    $lnk_lst->direction = 'up';
    $lnk_lst->load($debug-1);
    foreach ($lnk_lst->lst AS $lnk) {
      $xml_lnk = $xml->addChild('TRIPLE');
      if ($lnk->name <> '')        { $xml_lnk->addChild('NAME',        $lnk->name); }
      if ($lnk->description <> '') { $xml_lnk->addChild('DESCRIPTION', $lnk->description); }
      $xml_lnk->addChild('FROM',        $lnk->from_name);
      $xml_lnk->addChild('VERB',        $lnk->link_type->name);
      $xml_lnk->addChild('TO',          $lnk->to_name);
    }

    // 5. export all used formu relations
    $frm_lst = New formula_list;
    $frm_lst->phr_lst = $phr_lst_used;
    $frm_lst->usr     = $this->usr;
    $frm_lst->load($debug-1);
    foreach ($frm_lst->lst AS $frm) {
      $xml_frm = $xml->addChild('FORMULA');
      if ($frm->name <> '')        { $xml_frm->addChild('NAME',        $frm->name); }
      if ($frm->usr_text <> '')    { $xml_frm->addChild('EXPRESSION',  $frm->usr_text); }
      if ($frm->description <> '') { $xml_lnk->addChild('DESCRIPTION', $frm->description); }
      $frm_phr_lst = $frm->assign_phr_lst_direct();
      foreach ($frm_phr_lst->lst AS $phr) {
        if ($phr->id > 0) {
          $xml_frm->addChild('ASSIGNED_WORD', $phr->name());
        } else {
          $xml_frm->addChild('ASSIGNED_TRIPLE', $phr->name());
        }
      }
    }

    header("Content-type: text/xml");
    zu_debug("xml_file->export ... loaded.", $debug-10);
    
    /*
    
    todo:
    show all formula linked to the used words
    
    <zukunftcom_xml_data pod="zukunft.com" snap_time="2018-02-23">
    <value owner="Timon" excluded="true">
      <triple from="Zurich" verb="is a" to="Company">Zurich Insurance</phrase>
      <word>Sales</word>
      <time>2016</time>
      <source>Kraft Foods Full Year Results 2017</source>
      46000
      <user_value user="Timon" excluded="false">45000</user_value>
    </value>  
    
    
    Open questions:
    - export the change log?
    -
    
    */

    return $xml;    
  }
  

  
}

?>
