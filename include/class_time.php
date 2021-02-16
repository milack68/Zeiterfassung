<?php
/*******************************************************************************
 * Timestamp für alle anderen Berechnungen
/*******************************************************************************
 * Version 0.9
 * Author:  IT-Master GmbH
 * www.it-master.ch / info@it-master.ch
 * Copyright (c), IT-Master GmbH, All rights reserved
 *******************************************************************************/
class time
{
    public $_jahr;
    public $_monat;
    public $_monatname;
    public $_tag;
    public $_stunde;
    public $_minute;
    public $_sekunde;
    public $_timestamp;
    public $_letzterTag;
    public $_runden;

    public function __construct()
    {
        $this->_jahr = date("Y", time());
        $this->_monat = date("n", time());
        $this->_tag = date("j", time());
        $this->_stunde = date("H", time());
        $this->_minute = date("i", time());
        $this->_sekunde = 0;
        $this->_timestamp = mktime($this->_stunde, $this->_minute, $this->_sekunde, $this->_monat, $this->_tag, $this->_jahr);
        $this->_letzterTag = idate('d', mktime(0, 0, 0, ($this->_monat + 1), 0, $this->_jahr));
        $this->_runden = 0;
    }
    public function edit_accept($time, $settingday)
    {
        $lastday = mktime(0, 0, 0, date("n", time()), date("j", time()) - $settingday, date("Y", time()));
        if ($time >= $lastday) {
            return true;
        } elseif ($settingday == 0) {
            return true;
        } else {
            return false;
        }
    }
    public function set_timestamp($time)
    {
        $this->_jahr = date("Y", $time);
        $this->_monat = date("n", $time);
        $this->_tag = date("j", $time);
        $this->_stunde = date("H", $time);
        $this->_minute = date("i", $time);
        $this->_sekunde = 0;
        if (date("s", $time) == '59' and date("i", $time) == '59') {
            $this->_minute = '00';
            $this->_stunde = $this->_stunde + 1;
        }
        $this->_timestamp = $time;
        $this->_letzterTag = idate('d', mktime(0, 0, 0, ($this->_monat + 1), 0, $this->_jahr));
    }
    public function set_monatsname($strnamen)
    {
        $strnamen = explode(";", $strnamen);
        $this->_monatname = ($strnamen[$this->_monat - 1]);
    }
    public function get_now()
    {
        return time();
    }
    public function get_stunde_now()
    {
        return date("H", time());
    }
    public function get_minute_now()
    {
        return date("i", time());
    }
    public function get_lastmonth()
    {
        if ($this->_monat == 1) {
            $_arr = mktime(0, 0, 0, 12, 1, $this->_jahr - 1);
        } else {
            $_arr = mktime(0, 0, 0, $this->_monat - 1, 1, $this->_jahr);
        }
        //Monat - Zahl, Timestamp,, Jahreszahl
        return $_arr;
    }
    public function get_nextmonth()
    {
        if ($this->_monat == 12) {
            $_arr = mktime(0, 0, 0, 1, 1, $this->_jahr + 1);
        } else {
            $_arr = mktime(0, 0, 0, $this->_monat + 1, 1, $this->_jahr);
        }
        //Monat - Zahl, Timestamp,, Jahreszahl
        return $_arr;
    }

    public function mktime($_w_stunde, $_w_minute, $_w_sekunde, $_w_monat, $_w_tag, $_w_jahr)
    {
        // eingefügt von milack68: Gleitzeitrahmen von 6:00 bis 19:00 Uhr
        // Zeiten vor 6:00 werden mit 6:00 gebucht, Zeiten nach 19:00 Uhr werden mit 19:00 Uhr gebucht
        if ($_w_stunde < '6') {
            $_w_stunde = '6';
            $_w_minute = '00';
            $_w_sekunde = '00';
        }
        if ($_w_stunde >= '19') {
            $_w_stunde = '19';
            $_w_minute = '00';
            $_w_sekunde = '00';
        }
        //Ende eingefügt von milack68

        if ($_w_stunde == '24' and $_w_minute == '00') {
            $_w_stunde = '23';
            $_w_minute = '59';
            $_w_sekunde = '59';
        } elseif ($_w_stunde == '00' and $_w_minute == '00') {
            $_w_stunde = '00';
            $_w_minute = '00';
            $_w_sekunde = '01';
        }
        return mktime($_w_stunde, $_w_minute, $_w_sekunde, $_w_monat, $_w_tag, $_w_jahr);
    }

    public function save_time($_timestamp, $_ordnerpfad)
    {
        // eingefügt von milack68
        // prüft Zeit, ob VOR 6 Uhr oder NACH 19 Uhr gestempelt wurde und setzt diese Zeiten dann auf 6 bzw. 19 Uhr
        $stunde = date("G", $_timestamp);
        $minute = date("i", $_timestamp);
        $sekunde = date("s", $_timestamp);
        if ($stunde < '6') {
            $stunde = '6';
            $minute = '00';
            $sekunde = '00';
        }
        if ($stunde >= '19') {
            $stunde = '19';
            $minute = '00';
            $sekunde = '00';
        }
        $jahr = date("Y", $_timestamp);
        $monat = date('m', $_timestamp);
        $tag = date('d', $_timestamp);
        $_timestamp = mktime($stunde, $minute, $sekunde, $monat, $tag, $jahr);
        // Ende eingefügt von milack68
        $_zeilenvorschub = "\r\n";
        $_file = "./Data/" . $_ordnerpfad . "/Timetable/" . $this->_jahr . "." . $this->_monat;
        $fp = fopen($_file, "a+");
        fputs($fp, $_timestamp);
        fputs($fp, $_zeilenvorschub);
        fclose($fp);
    }

    public function set_runden($zahl)
    {
        $this->_runden = (int) $zahl;
    }
    public function save_quicktime($_ordnerpfad)
    {
        $_zeilenvorschub = "\r\n";
        $time = time();
        $_w_jahr = date("Y", $time);
        $_w_monat = date("n", $time);
        $_w_tag = date("j", $time);
        $_w_stunde = date("G", $time);
        $_w_minute = date("i", $time);
        $_w_sekunde = 0;
        // eingefügt von milack68
        // prüft Zeit, ob VOR 6 Uhr oder NACH 19 Uhr gestempelt wurde und setzt diese Zeiten dann auf 6 bzw. 19 Uhr
        if ($_w_stunde < '6') {
            $_w_stunde = '6';
            $_w_minute = '00';
        }
        if ($_w_stunde >= '19') {
            $_w_stunde = '19';
            $_w_minute = '00';
        }
        // Ende eingefügt von milack68

        $_file = "./Data/" . $_ordnerpfad . "/Timetable/" . $_w_jahr . "." . $_w_monat;
        // runden der Quicktime stempelzeit auf Minuten die in den Settings eingestellt ist
        if ($this->_runden) {
            //echo "Minuten : " . $_w_minute . "<br>";                             // Beispiel : 58
            $_neu = round($_w_minute / $this->_runden, 0) * $this->_runden; // Beispiel : 60
            $_von = $_neu - ($this->_runden / 2); // Beispiel : 55
            $_bis = $_von + $this->_runden; // Beispiel : 65
            $_w_minute = $_neu;
        }
        $_timestamp = mktime($_w_stunde, $_w_minute, $_w_sekunde, $_w_monat, $_w_tag, $_w_jahr);
        $fp = fopen($_file, "a+");
        // TODO : Sekundengenau stempeln, kann zu Berechnungsfehlern führen
        // TODO : Sekunden grösser als kommt und Minute geht kleiner als kommt, wird keine Stunde abgerechnet - Logik überprüfen)
        // Minutengenau
        fputs($fp, $_timestamp);
        fputs($fp, $_zeilenvorschub);
        fclose($fp);
        // Sekundengenau
        // $this->set_timestamp(time());
        // Minutengenau
        $this->set_timestamp($_timestamp);
    }

    public function update_stempelzeit($_oldtime, $_newtime, $_ordnerpfad)
    {
        $_zeilenvorschub = "\r\n";
        $_file = "./Data/" . $_ordnerpfad . "/Timetable/" . $this->_jahr . "." . $this->_monat;
        //Stempelzeiten in ein Array speichern
        if (!file_exists($_file)) {
            //echo "keine Daten vorhanden";
            //  TODO :  if bereinigen und testen
        } else {
            $_timeTable = file($_file);
        }
        $i = 0;
        foreach ($_timeTable as $_tmp) {
            if (trim($_tmp) == trim($_oldtime)) {
                $_timeTable[$i] = $_newtime . $_zeilenvorschub;
                $_oldtime = null;
            } elseif (trim($_tmp) == "") {
                // Leere zeile wird gelöscht, falls vorhanden
                // TODO : Fehler finden
                unset($_timeTable[$i]);
            }
            $i++;
        }
        $neu = implode("", $_timeTable);
        $open = fopen($_file, "w+");
        fwrite($open, $neu);
        fclose($open);
    }
    public function delete_stempelzeit($_oldtime, $_ordnerpfad)
    {
        $_file = "./Data/" . $_ordnerpfad . "/Timetable/" . $this->_jahr . "." . $this->_monat;
        //Stempelzeiten in ein Array speichern
        if (!file_exists($_file)) {
            // echo "<hr>keine Daten vorhanden<hr>";
            // TODO :  if bereinigen und testen
        } else {
            $_timeTable = file($_file);
        }
        $i = 0;
        foreach ($_timeTable as $_tmp) {
            if (trim($_tmp) == trim($_oldtime)) {
                unset($_timeTable[$i]);
                $_oldtime = null;
            } elseif (trim($_tmp) == "") {
                // Leere zeile wird gelöscht, falls vorhanden
                // TODO : Fehler finden
                unset($_timeTable[$i]);
            }
            $i++;
        }
        $neu = implode("", $_timeTable);
        $open = fopen($_file, "w+");
        fwrite($open, $neu);
        fclose($open);
    }
    public function save_timestamp($_timestamp, $_ordnerpfad)
    {
        $_zeilenvorschub = "\r\n";
        $jahr = date("Y", $_timestamp);
        $monat = date("n", $_timestamp);
        $_file = "./Data/" . $_ordnerpfad . "/Timetable/" . $jahr . "." . $monat;
        $fp = fopen($_file, "a+");
        fputs($fp, $_timestamp);
        fputs($fp, $_zeilenvorschub);
        fclose($fp);
    }
    public function checktime($_stunde, $_minute, $_monat, $_tag, $_jahr)
    {
        if ($_stunde == '24' && $_minute == '00') {
            $_stunde = 23;
            $_minute = 59;
            $_sekunde = 59;
            $_eintragen = mktime($_stunde, $_minute, $_sekunde, $_monat, $_tag, $_jahr);
        }
        return $_eintragen;
    }
    public function lasttime($_timestamp, $_ordnerpfad)
    {
        $jahr = date("Y", $_timestamp);
        $monat = date("n", $_timestamp);
        $_timeTable = null;
        $_file = "./Data/" . $_ordnerpfad . "/Timetable/" . $jahr . "." . $monat;
        // diesen Monat überprüfen
        if (file_exists($_file)) {
            $_timeTable = file($_file);
            // falls kein Eintrag. letzten Monat überprüfen
            $datum = $this->timecount($_timeTable);
        }
        // letzten Monat überprüfen falls in diesem keine Einträge drin sind
        if (count($_timeTable) < 1) {
            $monat = $monat - 1;
            $_file = "./Data/" . $_ordnerpfad . "/Timetable/" . $jahr . "." . $monat;
            if (file_exists($_file)) {
                $_timeTable = file($_file);
                $datum = $this->timecount($_timeTable);
            }
        }
        if ($datum) {
            return mktime(0, 0, 0, $monat, $datum, date("Y", $_timestamp));
        } else {
            return null;
        }
    }
    private function timecount($_timeTable)
    {
        $_lastday = null;
        rsort($_timeTable);
        $_count = 0;
        foreach ($_timeTable as $_tmp) {
            if (!$_lastday) {
                $_lastday = date('j', (int) trim($_tmp));
                $_count++;
            } elseif ($_lastday == date('j', (int) trim($_tmp))) {
                $_count++;
            }
        }
        if ($_count % 2) {
            //wenn eine Zeit fehlt den Tag zurückgeben
            return $_lastday;
        } else {
            //falls keine Zeit fehlt
            return null;
        }
    }

}
