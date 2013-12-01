<?php
/**
 * FRC Robots CAPTCHA
 * A CAPTCHA to test your FRC nerdiness
 *
 * Images provided by Idle Loop Software's FRC Tracker Photo Share (http://www.idleloop.com/frctracker/photos-view.php)
 * Data Copyright 2009-2013, Idle Loop Software Design, LLC
 *
 * @author Team 2485
*/

/**
 * Default config
 */
$frc_captcha_config = [
    'event_base_url' => 'http://www.idleloop.com/frctracker/photos-view.php',
    'photo_base_url' => 'http://www.idleloop.com/frctracker/',
    'year'           => '2013',
    'event_codes'    => ['TXSA','ORPO','NHMA','LAKE','MIBED','INWL','MABO','NJBRG','OHCL','CAMA','WASE2','MDBA','CODE','CTHA','INTH','TXDA','MIDET','CMP','Archimedes','Curie','Galileo','Newton','QCMO','NYRO','MIGBL','MOKC','ONTO','ONTO2','MIGUL','PAHAT','HIHO','TXLU','CASB','ISTA','MIKET','MNDU','NVLV','NJLEN','MILIV','TXHO','CALB','MICMP','MRCMP','ILCH','MNMI','MNMI2','NJFLA','NYNY','NCRE','MNDU2','OKOK','FLOR','SCMB','GADU','AZCH','MELE','PAPI','OHIC','ARFA','NYLI','CASA','CASD','WASE','CASJ','TNKN','FLBR','WACH','PAPHI','MISJO','MOSL','NJEWN','MITVC','MITRY','UTWV','VARI','MAWO','DCWA','MIWFD','ONWA','MIWMI','ABCA','WIMI']
];

/**
 * Generates a team number and image for CAPTCHA use.
 *
 * It saves the image src into $_SESSION['frc_captcha_src'] and the team number
 * into $_SESSION['frc_captcha_team'].
 * PHP must have access to http://www.idleloop.com. This may throw errors if
 * the site layout changes, as it parses the HTML of the event pages for team
 * information.
 *
 * @uses $frc_captcha_config
 * @return array with keys 'src', 'team' containing the image src and team number.
 */
function frc_captcha() {
	global $frc_captcha_config;

    // Choose the event and get its html
    $event = $frc_captcha_config['event_codes'][mt_rand(0, count($frc_captcha_config['event_codes']) - 1)];
    $event_html = file_get_contents($frc_captcha_config['event_base_url'] . '?year=' . $frc_captcha_config['year'] . '&event_code=' . $event);

    // Find the number of teams in this event
    // "<p>Photos for [100] of 100 teams:</p>"
    $num_teams_start = 'Photos for ';
    $index = strpos($event_html, $num_teams_start) + strlen($num_teams_start);
    $num_teams = intval( substr($event_html, $index, strpos($event_html, ' ', $index) - $index) );

    // Choose the team index on the page
    $team_index = mt_rand(0, $num_teams - 1);

    // Find the team's html and number
    // "<td class="team_info_cell"><span class="team_number">Team [2485]</span><br>WARLords<br>San Diego, CA<br>Rookie Year: 2008</td>"
    $team_num_start = '<td class="team_info_cell"><span class="team_number">Team ';
    $count = 0;
    do {
        $index = strpos($event_html, $team_num_start, $index) + strlen($team_num_start);
    } while ($count++ < $team_index);
    $team_num = substr($event_html, $index, strpos($event_html, '<', $index) - $index);

    // Find the team's image url
    // "<td class="team_photo_cell"><img src="[photoshare/robot_photos/2013/4131.jpg]" width="320" height="367"></td>"
    $imgsrc_start = '<img src="';
    $index = strpos($event_html, $imgsrc_start, $index) + strlen($imgsrc_start);
    $img_src = substr($event_html, $index, strpos($event_html, '"', $index) - $index);

    // Get the absolute URL
    $image_src = $frc_captcha_config['photo_base_url'] . $img_src;

    if (session_id() === '') session_start();
    $_SESSION['frc_captcha_src']  = $image_src;
    $_SESSION['frc_captcha_team'] = $team_num;

    return [
        'src'  => $image_src,
        'team' => $team_num
    ];
}

/**
 * Echoes a CAPTCHA widget to be styled by you.
 *
 * Note that CAPTCHA images are always 320px wide, but vary in height.
 *
 * @uses frc_captcha() to generate the CAPTCHA info.
 */
function frc_captcha_widget() {
    $src = frc_captcha()['src'];
    echo <<<END
<div class="frc-captcha-widget">
    <div class="frc-captcha-image">
        <img src="$src">
    </div>
    <div class="frc-captcha-input">
        <input name="frc_captcha_team" type="text" placeholder="Team Number">
    </div>
</div>
END;
}

/**
 * Checks if the team matches the user's input.
 *
 * It gets the $_SESSION data (saved by frc_captcha()) and checks if it matches
 * the team POSTed to the page (via a frc_captcha_team variable) or the value passed to the function.
 *
 * @param string $input The value to check.
 * @return bool Whether the input is correct.
 */
function frc_captcha_verify($input = null) {
    if ($input == null) {
    	if (isset($_POST['frc_captcha_team'])) {
    		$input = $_POST['frc_captcha_team'];
    	}
    	else return false;
    }

    if (session_id() === '') session_start();
    return isset($_SESSION['frc_captcha_team']) && $_SESSION['frc_captcha_team'] == $input;
}
