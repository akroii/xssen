<?php
/* ******************************************************* */
/*                          xsSen.php                      */
/*                 einfacher Verzeichnisschutz.            */
/* ******************************************************* */
/*  VOR GEBRAUCH LIZENS- UND NUTZUNGSBEDINGUNGEN BEACHTEN  */
/*         www.sensiebels.de/download/lizenz.htm           */
/*         www.sensiebels.de/download/nutzung.htm          */
/* ******************************************************* */
##########################################################################################
# Dieses Programm ist freie Software. Sie k&ouml;nnen es unter den Bedingungen der GNU General
# Public License, wie von der Free Software Foundation ver&ouml;ffentlicht, weitergeben
# und/oder modifizieren, entweder gem&auml;&szlig; Version 2 der Lizenz oder (nach Ihrer Option)
# jeder sp&auml;teren Version.
#
# Die Ver&ouml;ffentlichung dieses Programms erfolgt in der Hoffnung, da&szlig; es Ihnen von Nutzen
# sein wird, aber OHNE IRGENDEINE GARANTIE, sogar ohne die implizite Garantie der
# MARKTREIFE oder der VERWENDBARKEIT F&Uuml;R EINEN BESTIMMTEN ZWECK. Details finden Sie in
# der GNU General Public License.
#
# Sie sollten eine Kopie der GNU General Public License zusammen mit diesem Programm erhalten
# haben. Falls nicht, schreiben Sie an die Free Software Foundation, Inc., 59 Temple
# Place, Suite 330, Boston, MA 02111-1307, USA.
##########################################################################################
# ENGLISH VERSION: http://www.gnu.org/licenses/gpl.html
##########################################################################################

/*   Die unten stehnden Stylesheets koennen Sie aendern   */  $stylesheets='

<style type="text/css">
<!--
body,i,u,b,p,table,td,form,input,textarea {
  font-family:courier new,courier,monospace;
  font-size:12px;
  color:#000000;
}
a {
  font-family:courier new,courier,monospace;
  font-size:12px;
  color:#00A000;
}
h1 {
  font-family:courier new,courier,monospace;
  font-size:28px;
  color:#0000C0;
}
pre {
  font-family:courier new,courier,monospace;
  font-size:12px;
  color:#0000C0;
}
.red {
  color:#FF0000;
  font-family:courier new,courier,monospace;
}
.url {
  color:#000000;
  font-family:arial,sans-serif;
  font-size:13px;
}
-->
</style>

';/* ********** Ende des Stylesheet - Bereichs *********** */

/**
*   Es sind KEINE weiteren Aenderungen oder Einstellungen am folgenden Programm vorzunehmen. Kopieren Sie diese Datei einfach in das zu sch&uuml;tzende Verzeichnis und fuehren Sie sie &uuml;ber den Browser aus.Eine genauere Anleitung finden Sie hier: http://www.sensiebels.de/download/xssen/
**/
/**
* Anpassung an PHP 7:
* Ersetzen "eregi_replace()" durch "preg_replace()" Zeilen: 94, 233, 291
* Ersetzen "$bereich = ereg_replace("[ ]+"," ",eregi_replace("[^a-z0-9]"," ",$bereich));" durch "$bereich = preg_replace("/[^A-Za-z0-9_]/"," ",$bereich);" Zeile 233
**/

// VERSION
$program = "xssen.php";
$version = "1.1.0 beta";	// Anpassung an PHP 7 gon GN2-Netwerk, 22.05.2017

// name dieses Scripts
$self_name = basename(__FILE__);
$self_pfad = realpath("./");

// Dateispeicher leeren
clearstatcache();

// demo aktivieren ./. nicht
if(file_exists($self_pfad.'/dvlp.inc')) {
   @include('dvlp.inc');
} else {
   $dvlp = "off";
}

// php version checken
if (substr(preg_replace("[^0-9]",'',PHP_VERSION),0,2) < 4.1){	// ersetzt: if (substr(eregi_replace("[^0-9]",'',PHP_VERSION),0,2) < 41){
    die ('Dieses Script erfordert mindestens PHP Version 4.1.0. Auf diesem Server ist Version '.PHP_VERSION.' installiert.');
}
// REQUEST vars
$request_var_arr = array('dvlp','dvlp_info','name','pw1','pw2','bereich','aufruf','showdetails','bericht','showpasswd','dvlpcryptdir','submit','cryptby','cpassword','killme','dvlpendtxt');
foreach ($request_var_arr as $val) {
    if(isset($_POST[$val])) {
        $$val = $_POST[$val];
    } elseif(isset($_GET[$val])) {
        $$val = $_GET[$val];
    } elseif(!isset($$val)) {
        $$val = '';
    }
}

?>
<html>
<head>
<meta name="robots" content="none">
<meta name="robots" content="noindex">
<title>xsSen.php - einfacher Verzeichnisschutz.</title>
<?php echo $stylesheets ?>
</head>
<body>
<div align="center">
<table width="650">
<tr>
<td align="center">
<form action="<?php echo $self_name ?>" method="post">
<h1>xsSen.php <?php echo $version ?></h1>
<i> Einfacher Verzeichnisschutz.<?php echo $dvlp_info ?><br>
<a href="http://www.sensiebels.de" target="_top">(P) (c) Copyright 2002 by Sensiebels</a></i>
<hr noshade size="1">
<?php
$name = trim(stripslashes($name));
$bereich = trim(stripslashes($bereich));
$pw1 = trim(stripslashes($pw1));
$pw2 = trim(stripslashes($pw2));

function submitbutton($buttontext,$aufruf) {
    global $name,$bereich,$pw1,$pw2;
    echo '
      <input type="hidden" name="bereich" value="',$bereich,'">
      <input type="hidden" name="name" value="',$name,'">
      <input type="hidden" name="pw1" value="',$pw1,'">
      <input type="hidden" name="pw2" value="',$pw2,'">
      <input type="hidden" name="aufruf" value="',$aufruf,'">
      <input type="submit" name="submit" value="',$buttontext,'">
    ';
}

/* PROGRAMM AUFRUFE */

switch ($aufruf) {
case "drei":

/* DRITTER AUFRUF: MACH ES WAHR! (ODER NICHT) */

    // abbrechen
    if($submit=="abbrechen") {
        ?>
        Das Verzeichnis wurde nicht gesch&uuml;tzt.<br>
        Es wurden weder Daten gespeichert noch ge&auml;ndert. <br><br>
        <?php
        submitbutton("zur&uuml;ck","eins");
    }
    else {
        // wahrmachen
        $dvlpcryptdir!="" ? $self_pfad = $dvlpcryptdir : $self_pfad;
        $htaccess = "AuthName \"".$bereich."\"\nAuthType Basic\nAuthUserFile "
                  . $self_pfad."/.htpasswd\n\nrequire valid-user\n";

        // VERSCHLUESSELN
        switch($cryptby) {
        case "crypt": $cpassword = crypt($pw1); break;
        case "md5": $cpassword = md5($pw1); break;
        case "none": $cpassword = $pw1; break;
        }
        $htpasswd = $name.":".$cpassword;

        //SCHREIBEN
        if($dvlp=='off') {
            if($datei1=@fopen(".htpasswd","w")) {
                fputs($datei1,$htpasswd);
                fclose($datei1);
                $bericht .= "Die .htpasswd Datei wurde geschrieben.<br>";
                if($datei2=@fopen(".htaccess","w")) {
                    fputs($datei2,$htaccess);
                    fclose($datei2);
                    $bericht .= "Die .htaccess Datei wurde geschrieben.<br>";
                }
                else {
                    $bericht.="<b>FEHLER.<br>Die .htaccess Datei konnte nicht
                               geschrieben werden!</b>";
                }
            }
            else {
                $bericht.="<b>FEHLER.<br>Die .htpasswd konnte nicht
                           geschrieben werden!</b>";
            }
        }
        else {
            // DVL == ON
            $bericht .= "Die .htpasswd Datei wurde geschrieben. (demo)<br>
                         Die .htaccess Datei wurde geschrieben. (demo)<br>";
        }
        //AUSGABE
        echo '<h1>BERICHT</h1><b>',$bericht,
        '</b><br><table><tr><td>
            <pre><b>Inhalt der Datei .htaccess:</b>',
             "\n", htmlspecialchars($htaccess),
            '</pre><hr noshade size="1"><pre><b>Inhalt der Datei .htpasswd:</b>',
             "\n", htmlspecialchars($htpasswd),
            '</pre>
        </td></tr></table><br>';

        // SELBSTZERSTOERUNG
        if($killme == "yes" && $dvlp!="on") {
            if (@unlink($self_name)) {
                echo "<br><b>Dieses Programm hat sich soeben selbst gel&ouml;scht.</b>";
            }
            else {
                // COULDN'T UNLINK
                echo "<br>Dieses Programm konnte nicht gel&ouml;scht werden und bleibt im
                      Verzeichnis bestehen. <b>Bitte entfernen Sie es sicherheitshalber manuell</b>!";
            }

        }
        else {
           // $killme == no
           echo "<br>Dieses Programm bleibt weiterhin im Verzeichnis bestehen.";
        }
        echo '<br><a href="../">../ ein Verzeichnis aufw&auml;rts</a>', $dvlpendtxt;
    }
break;
########################################################################
case "zwei":
/* PRUEFEN */

    $bereich = preg_replace("/[^A-Za-z0-9_]/"," ",$bereich);	// ersetzt: $bereich = ereg_replace("[ ]+"," ",eregi_replace("[^a-z0-9]"," ",$bereich));

    // VARIABLEN UEBERGEBEN?
    if(!$pw1||!$pw2||!$name||!$bereich) {
        echo "<b>Bitte f&uuml;llen Sie alle Felder aus.</b><br>";
        submitbutton("zur&uuml;ck","eins");
    }
    else {
        // PASSWOERTER RICHTIG?
        if($pw1!=$pw2) {
        echo "<b>Die Passw&ouml;rter stimmen nicht &uuml;berein.<br><br><i>
                   <a href=\"",$self_name,"\">Neustart</a></i></b>";
        }
        else {
            // FILE EXISTIERT?
            if(file_exists($self_pfad."/.htaccess")) {
                echo "<b class=\"red\">ACHTUNG</b>
                      <br>Es existiert bereits eine <b>.htaccess</b> Datei. Wenn Sie
                      fortfahren, wird diese &uuml;berschrieben!<hr noshade size=\"1\">";
            }
            if(file_exists($self_pfad."/.htpasswd")) {
                echo "<b class=\"red\">ACHTUNG</b>
                      <br>Es existiert bereits eine <b>.htpasswd</b> Datei. Wenn Sie
                      fortfahren, wird diese &uuml;berschrieben!<hr noshade size=\"1\">";
            }

            // ZUR UEBERPRUEFUNG AUSGEBEN
            ?>
            <table><tr><td align="right">
              <table cellspacing="0" cellpadding="6" border="1">
                <tr>
                  <th colspan="2">Bitte w&auml;hlen Sie die Art der Passwort-Verschl&uuml;sselung: </th>
                </tr><tr>
                  <td><input type="radio" name="cryptby" value="crypt" checked></td> <td>crypt (linux, unix)</td>
                </tr><tr>
                  <td><input type="radio" name="cryptby" value="md5"></td> <td>md5 (windows)</td>
                </tr><tr>
                  <td><input type="radio" name="cryptby" value="none"></td> <td>unverschl&uuml;sselt</td>
                </tr>
              </table>
            </td><td align="left">
              <table cellspacing="0" cellpadding="6" border="1">
                <tr>
                  <th colspan="2" align="center">
                     Bitte pr&uuml;fen Sie diese Angaben nochmals und merken Sie sich Ihre Zugangsdaten:
                  </th>
                </tr><tr>
                  <td colspan="2" align="center">Name des Bereichs: <?php echo $bereich ?></td>
                </tr><tr>
                  <td>USERNAME:</td> <td><?php echo $name ?></td>
                </tr><tr>
                  <td>PASSWORT:</td>
                  <td>
                  <?php
                  if($showpasswd!="no") {
                     echo $pw1;
                  }
                  else {
                     echo preg_replace("[^\*]","*",$pw1);	// ersetzt: echo eregi_replace("[^\*]","*",$pw1);
                  }
                  ?>
                  </td>
                </tr>
              </table>
            </td></tr></table><br>
            <?php
            submitbutton("Verzeichnisschutz erstellen! *","drei");
            ?>
              <br>
              <table><tr><td>
                  <input type="checkbox" name="killme" value="yes" checked>
              </td><td>
                  * Dieses Programm nach erfolgter Erstellung entfernen (empfohlen).
              </td></tr></table>
              <br>
              <hr noshade size="1">
              <input type="submit" name="submit" value="abbrechen">
            <?php
            $bericht ? $bericht="<b>HINWEISE:</b><hr noshade size=\"1\">".$bericht:$bericht="";
        }
    }

break;
########################################################################
case "eins":
/* NAME & PASSWORTEINGABE */

   // http-pfadangabe
   $httppfad_null = explode("?",getenv('HTTP_REFERER'));
   $httppfad = $httppfad_null[0];

   // defaulteintrag f&uuml;r form[bereich]
   $bereich == "" ? $bereich = "PRIVAT" : $bereich;
   ?>
   Stimmt folgende Adresse mit der Adressleiste Ihres Browsers &uuml;berein?<br>
           <table border="1" cellpadding="3"><tr><td>
           <span class="url"><?php echo $httppfad ?></span>
           </td></tr></table><br><br>
   Wenn <b>ja:</b> Tragen Sie bitte einen Benutzernamen und ein Passwort ein:<br><br>
   <table>
     <tr>
       <td>Benutzername:</td>
       <td><input type="text" size="35" name="name" value="<?php echo $name ?>" maxlength="20"></td>
     </tr><tr>
       <td>Passwort:</td>
       <td><input type="password" name="pw1" value="" size="35" maxlength="20"></td>
     </tr><tr>
       <td>Passwort wiederholen:</td>
       <td><input type="password" name="pw2" value="" size="35" maxlength="20"></td>
     </tr><tr>
       <td>Name des gesch&uuml;tzten Bereichs:</td>
       <td><input type="text" name="bereich" value="<?php echo $bereich ?>" size="35" maxlength="20"></td>
     </tr>
     <tr><td colspan="2">(wird sp&auml;ter bei der Passwortabfrage angezeigt)</td></tr>
   </table><br>
   <input type="hidden" name="aufruf" value="zwei">
   <hr noshade size="1">
   <table><tr>
     <td><input type="Checkbox" name="showpasswd" value="no" checked></td>
     <td>Passwort auch im n&auml;chsten Schritt als *** anzeigen.</td>
   </tr></table>
   <input type="submit" value="weiter">
   <?php
break;
########################################################################
default:
   /* NULLAUFRUF */
   if($self_name!='' && $self_pfad!='' && file_exists($self_pfad."/".$self_name)) {
        if ($showdetails=="yes") {
            echo '<P><STRONG>NO WARRANTY</STRONG></P><P><STRONG>11.</STRONG> BECAUSE THE PROGRAM IS LICENSED FREE OF CHARGE, THERE IS NO WARRANTY FOR THE PROGRAM, TO THE EXTENT PERMITTED BY APPLICABLE LAW.  EXCEPT WHEN OTHERWISE STATED IN WRITING THE COPYRIGHT HOLDERS AND/OR OTHER PARTIES PROVIDE THE PROGRAM "AS IS" WITHOUT WARRANTY OF ANY KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE.  THE ENTIRE RISK AS TO THE QUALITY AND PERFORMANCE OF THE PROGRAM IS WITH YOU.  SHOULD THE PROGRAM PROVE DEFECTIVE, YOU ASSUME THE COST OF ALL NECESSARY SERVICING, REPAIR OR CORRECTION. <P> <STRONG>12.</STRONG> IN NO EVENT UNLESS REQUIRED BY APPLICABLE LAW OR AGREED TO IN WRITING WILL ANY COPYRIGHT HOLDER, OR ANY OTHER PARTY WHO MAY MODIFY AND/OR REDISTRIBUTE THE PROGRAM AS PERMITTED ABOVE, BE LIABLE TO YOU FOR DAMAGES, INCLUDING ANY GENERAL, SPECIAL, INCIDENTAL OR CONSEQUENTIAL DAMAGES ARISING OUT OF THE USE OR INABILITY TO USE THE PROGRAM (INCLUDING BUT NOT LIMITED TO LOSS OF DATA OR DATA BEING RENDERED INACCURATE OR LOSSES SUSTAINED BY YOU OR THIRD PARTIES OR A FAILURE OF THE PROGRAM TO OPERATE WITH ANY OTHER PROGRAMS), EVEN IF SUCH HOLDER OR OTHER PARTY HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES.<br><br><a target="_blank" href="http://www.gnu.org/licenses/gpl.html">READ THE GNU GENERAL PUBLIC LICENCE</a><hr noshade size="1"><a href="',$self_name,'">[hide details]</a><hr noshade size="1">';
        }
        else {
            echo $program,' comes with ABSOLUTELY NO WARRANTY - <a href="',$self_name,'?showdetails=yes">for details click here</a><hr noshade size="1">F&uuml;r ',$program,' besteht KEINERLEI GARANTIE - <a href="',$self_name,'?showdetails=yes">Klicken Sie hier f&uuml;r Details.</a><hr noshade size="1"><a target="_blank" href="http://www.gnu.org/licenses/gpl.html">GNU GENERAL PUBLIC LICENSE</a><hr noshade size="1">';
        }
       ?>
       <br><input type="hidden" name="aufruf" value="eins">
       <input type="submit" value="start">
       <br><br> Es folgen zwei weitere Schritte.
       <?php
   }
   else {
       ?>
       <h1>Fehler.</h1>
       Dieses Script funktioniert leider nicht auf diesem Server.<br>Schauen Sie auf
       <a href="http://www.sensiebels.de/download/">www.sensiebels.de/download/</a>
       nach, ob eine neuere Version (&nbsp;&gt;&nbsp;<?php echo $version ?>&nbsp;)
       dieses Scripts verf&uuml;gbar ist.<br><br>Danke f&uuml;r Ihr Verst&auml;ndnis.
       <?php
   }
break;
########################################################################
} // ende switch
?>
</form>
</td></tr></table>
</div>
</body>
</html>
