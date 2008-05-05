<?php
/*
Plugin Name: WP_Identicon
Version: 1.02
Plugin URI: http://scott.sherrillmix.com/blog/blogger/wp_identicon/
Description: This plugin generates persistent specific geometric icons for each user based on the ideas of <a href="http://www.docuverse.com/blog/donpark/2007/01/18/visual-security-9-block-ip-identification">Don Park</a>.
Author: Scott Sherrill-Mix
Author URI: http://scott.sherrillmix.com/blog/
*/

define('WP_IDENTICON_DIR',  str_replace('\\','/',preg_replace('@.*([\\\\/]wp-content[\\\\/].*)@','\1',dirname(__FILE__)).'/identicon/'));
define('WP_IDENTICON_DIR_INTERNAL', dirname(__FILE__).'/identicon/');


function identicon_menu() {
	if (function_exists('add_options_page')) {
		add_options_page('Identicon Control Panel', 'Identicon', 1, basename(__FILE__), 'identicon_subpanel');
	}
}

class identicon {
	var $identicon_options;
	var $blocks;
	var $shapes;
	var $rotatable;
	var $square;
	var $im;
	var $colors;
	var $size;
	var $blocksize;
	var $quarter;
	var $half;
	var $diagonal;
	var $halfdiag;
	var $transparent=false;
	var $centers;
	var $shapes_mat;
	var $symmetric_num;
	var $rot_mat;
	var $invert_mat;
	var $rotations;

	//constructor
	function identicon($blocks='') {
		$this->identicon_options=identicon_get_options();
		if ($blocks) $this->blocks=$blocks; 
		else $this->blocks=$this->identicon_options['squares'];
		$this->blocksize=80;
		$this->size=$this->blocks*$this->blocksize;
		$this->quarter=$this->blocksize/4;
		$this->half=$this->blocksize/2;
		$this->diagonal=sqrt($this->half*$this->half+$this->half*$this->half);
		$this->halfdiag=$this->diagonal/2;
		$this->shapes=array(
			array(array(array(90,$this->half),array(135,$this->diagonal),array(225,$this->diagonal),array(270,$this->half))),//0 rectangular half block
			array(array(array(45,$this->diagonal),array(135,$this->diagonal),array(225,$this->diagonal),array(315,$this->diagonal))),//1 full block
			array(array(array(45,$this->diagonal),array(135,$this->diagonal),array(225,$this->diagonal))),//2 diagonal half block
			array(array(array(90,$this->half),array(225,$this->diagonal),array(315,$this->diagonal))),//3 triangle
			array(array(array(0,$this->half),array(90,$this->half),array(180,$this->half),array(270,$this->half))),//4 diamond
			array(array(array(0,$this->half),array(135,$this->diagonal),array(270,$this->half),array(315,$this->diagonal))),//5 stretched diamond
			array(array(array(0,$this->quarter),array(90,$this->half),array(180,$this->quarter)), array(array(0,$this->quarter),array(315,$this->diagonal),array(270,$this->half)), array(array(270,$this->half),array(180,$this->quarter),array(225,$this->diagonal))),// 6 triple triangle
			array(array(array(0,$this->half),array(135,$this->diagonal),array(270,$this->half))),//7 pointer
			array(array(array(45,$this->halfdiag),array(135,$this->halfdiag),array(225,$this->halfdiag),array(315,$this->halfdiag))),//9 center square
			array(array(array(180,$this->half),array(225,$this->diagonal),array(0,0)), array(array(45,$this->diagonal),array(90,$this->half),array(0,0))),//9 double triangle diagonal
			array(array(array(90,$this->half),array(135,$this->diagonal),array(180,$this->half),array(0,0))),//10 diagonal square
			array(array(array(0,$this->half),array(180,$this->half),array(270,$this->half))),//11 quarter triangle out
			array(array(array(315,$this->diagonal),array(225,$this->diagonal),array(0,0))),//12quarter triangle in
			array(array(array(90,$this->half),array(180,$this->half),array(0,0))),//13 eighth triangle in
			array(array(array(90,$this->half),array(135,$this->diagonal),array(180,$this->half))),//14 eighth triangle out
			array(array(array(90,$this->half),array(135,$this->diagonal),array(180,$this->half),array(0,0)), array(array(0,$this->half),array(315,$this->diagonal),array(270,$this->half),array(0,0))),//15 double corner square
			array(array(array(315,$this->diagonal),array(225,$this->diagonal),array(0,0)), array(array(45,$this->diagonal),array(135,$this->diagonal),array(0,0))),//16 double quarter triangle in
			array(array(array(90,$this->half),array(135,$this->diagonal),array(225,$this->diagonal))),//17 tall quarter triangle
			array(array(array(90,$this->half),array(135,$this->diagonal),array(225,$this->diagonal)), array(array(45,$this->diagonal),array(90,$this->half),array(270,$this->half))),//18 double tall quarter triangle
			array(array(array(90,$this->half),array(135,$this->diagonal),array(225,$this->diagonal)), array(array(45,$this->diagonal),array(90,$this->half),array(0,0))),//19 tall quarter + eighth triangles
			array(array(array(135,$this->diagonal),array(270,$this->half),array(315,$this->diagonal))),//20 tipped over tall triangle
			array(array(array(180,$this->half),array(225,$this->diagonal),array(0,0)), array(array(45,$this->diagonal),array(90,$this->half),array(0,0)), array(array(0,$this->half),array(0,0),array(270,$this->half))),//21 triple triangle diagonal
			array(array(array(0,$this->quarter),array(315,$this->diagonal),array(270,$this->half)), array(array(270,$this->half),array(180,$this->quarter),array(225,$this->diagonal))),//22 double triangle flat
			array(array(array(0,$this->quarter),array(45,$this->diagonal),array(315,$this->diagonal)), array(array(180,$this->quarter),array(135,$this->diagonal),array(225,$this->diagonal))),//23 opposite 8th triangles
			array(array(array(0,$this->quarter),array(45,$this->diagonal),array(315,$this->diagonal)), array(array(180,$this->quarter),array(135,$this->diagonal),array(225,$this->diagonal)), array(array(180,$this->quarter),array(90,$this->half),array(0,$this->quarter),array(270,$this->half))),//24 opposite 8th triangles + diamond
			array(array(array(0,$this->quarter),array(90,$this->quarter),array(180,$this->quarter),array(270,$this->quarter))),//25 small diamond
			array(array(array(0,$this->quarter),array(45,$this->diagonal),array(315,$this->diagonal)), array(array(180,$this->quarter),array(135,$this->diagonal),array(225,$this->diagonal)), array(array(270,$this->quarter),array(225,$this->diagonal),array(315,$this->diagonal)),array(array(90,$this->quarter),array(135,$this->diagonal),array(45,$this->diagonal))),//26 4 opposite 8th triangles
			array(array(array(315,$this->diagonal),array(225,$this->diagonal),array(0,0)), array(array(0,$this->half),array(90,$this->half),array(180,$this->half))),//27 double quarter triangle parallel
			array(array(array(135,$this->diagonal),array(270,$this->half),array(315,$this->diagonal)), array(array(225,$this->diagonal),array(90,$this->half),array(45,$this->diagonal))),//28 double overlapping tipped over tall triangle
			array(array(array(90,$this->half),array(135,$this->diagonal),array(225,$this->diagonal)), array(array(315,$this->diagonal),array(45,$this->diagonal),array(270,$this->half))),//29 opposite double tall quarter triangle
			array(array(array(0,$this->quarter),array(45,$this->diagonal),array(315,$this->diagonal)), array(array(180,$this->quarter),array(135,$this->diagonal),array(225,$this->diagonal)), array(array(270,$this->quarter),array(225,$this->diagonal),array(315,$this->diagonal)),array(array(90,$this->quarter),array(135,$this->diagonal),array(45,$this->diagonal)),array(array(0,$this->quarter),array(90,$this->quarter),array(180,$this->quarter),array(270,$this->quarter))),//30 4 opposite 8th triangles+tiny diamond
			array(array(array(0,$this->half),array(90,$this->half),array(180,$this->half),array(270,$this->half), array(270,$this->quarter),array(180,$this->quarter),array(90,$this->quarter),array(0,$this->quarter))),//31 diamond C
			array(array(array(0,$this->quarter),array(90,$this->half),array(180,$this->quarter),array(270,$this->half))),//32 narrow diamond
			array(array(array(180,$this->half),array(225,$this->diagonal),array(0,0)), array(array(45,$this->diagonal),array(90,$this->half),array(0,0)), array(array(0,$this->half),array(0,0),array(270,$this->half)), array(array(90,$this->half),array(135,$this->diagonal),array(180,$this->half))),//33 quadruple triangle diagonal
			array(array(array(0,$this->half),array(90,$this->half),array(180,$this->half),array(270,$this->half),array(0,$this->half), array(0,$this->quarter),array(270,$this->quarter),array(180,$this->quarter),array(90,$this->quarter),array(0,$this->quarter))),//34 diamond donut
			array(array(array(90,$this->half),array(45,$this->diagonal),array(0,$this->quarter)), array(array(0,$this->half),array(315,$this->diagonal),array(270,$this->quarter)), array(array(270,$this->half),array(225,$this->diagonal),array(180,$this->quarter))),//35 triple turning triangle
			array(array(array(90,$this->half),array(45,$this->diagonal),array(0,$this->quarter)), array(array(0,$this->half),array(315,$this->diagonal),array(270,$this->quarter))),//36 double turning triangle
			array(array(array(90,$this->half),array(45,$this->diagonal),array(0,$this->quarter)), array(array(270,$this->half),array(225,$this->diagonal),array(180,$this->quarter))),//37 diagonal opposite inward double triangle
			array(array(array(90,$this->half),array(225,$this->diagonal),array(0,0),array(315,$this->diagonal))),//38 star fleet
			array(array(array(90,$this->half),array(225,$this->diagonal),array(0,0),array(315,$this->halfdiag),array(225,$this->halfdiag), array(225,$this->diagonal),array(315,$this->diagonal))),//39 hollow half triangle
			array(array(array(90,$this->half),array(135,$this->diagonal),array(180,$this->half)), array(array(270,$this->half),array(315,$this->diagonal),array(0,$this->half))),//40 double eighth triangle out
			array(array(array(90,$this->half),array(135,$this->diagonal),array(180,$this->half),array(180,$this->quarter)), array(array(270,$this->half),array(315,$this->diagonal),array(0,$this->half),array(0,$this->quarter))),//42 double slanted square
			array(array(array(0,$this->half),array(45,$this->halfdiag), array(0,0),array(315,$this->halfdiag)), array(array(180,$this->half),array(135,$this->halfdiag), array(0,0),array(225,$this->halfdiag))),//43 double diamond
			array(array(array(0,$this->half),array(45,$this->diagonal), array(0,0),array(315,$this->halfdiag)), array(array(180,$this->half),array(135,$this->halfdiag), array(0,0),array(225,$this->diagonal))),//44 double pointer
		);
		$this->rotatable=array(1,4,8,25,26,30,34);
		$this->square=$this->shapes[1][0];	
		$this->symmetric_num=ceil($this->blocks*$this->blocks/4);
		for ($i=0;$i<$this->blocks;$i++){
			for ($j=0;$j<$this->blocks;$j++){
				$this->centers[$i][$j]=array($this->half+$this->blocksize*$j,$this->half+$this->blocksize*$i);
				$this->shapes_mat[$this->xy2symmetric($i,$j)]=1;
				$this->rot_mat[$this->xy2symmetric($i,$j)]=0;
				$this->invert_mat[$this->xy2symmetric($i,$j)]=0;
				if (floor(($this->blocks-1)/2-$i)>=0&floor(($this->blocks-1)/2-$j)>=0&($j>=$i|$this->blocks%2==0)){
					$inversei=$this->blocks-1-$i;
					$inversej=$this->blocks-1-$j;
					$symmetrics=array(array($i,$j),array($inversej,$i),array($inversei,$inversej),array($j,$inversei));
					$fill=array(0,270,180,90);
					for ($k=0;$k<count($symmetrics);$k++){
						$this->rotations[$symmetrics[$k][0]][$symmetrics[$k][1]]=$fill[$k];
					}
				}
			}
		}
	}
	
	function xy2symmetric($x,$y){
		$index=array(floor(abs(($this->blocks-1)/2-$x)),floor(abs(($this->blocks-1)/2-$y)));
		sort($index);
		$index[1]*=ceil($this->blocks/2);
		$index=array_sum($index);
		return $index;
	}
	


	//convert array(array(heading1,distance1),array(heading1,distance1)) to array(x1,y1,x2,y2)
	function identicon_calc_x_y($array,$centers,$rotation=0){
		$output=array();
		$centerx=$centers[0];
		$centery=$centers[1];
		while($thispoint=array_pop($array)){
			$y=round($centery+sin(deg2rad($thispoint[0]+$rotation))*$thispoint[1]);
			$x=round($centerx+cos(deg2rad($thispoint[0]+$rotation))*$thispoint[1]);
			array_push($output,$x,$y);
		}
		return $output;
	}

	//draw filled polygon based on an array of (x1,y1,x2,y2,..)
	function identicon_draw_shape($x,$y){ 
		$index=$this->xy2symmetric($x,$y);
		$shape=$this->shapes[$this->shapes_mat[$index]];
		$invert=$this->invert_mat[$index];
		$rotation=$this->rot_mat[$index];
		$centers=$this->centers[$x][$y];
		$invert2=abs($invert-1);
		$points=$this->identicon_calc_x_y($this->square,$centers,0);
		$num = count($points) / 2;
		imagefilledpolygon($this->im, $points, $num, $this->colors[$invert2]);
		foreach($shape as $subshape){
			$points=$this->identicon_calc_x_y($subshape,$centers,$rotation+$this->rotations[$x][$y]);
			$num = count($points) / 2;
			imagefilledpolygon($this->im, $points, $num,$this->colors[$invert]);
		}
	}

	//use a seed value to determine shape, rotation, and color
	function identicon_set_randomness($seed=""){
		//set seed
		$twister=new identicon_mersenne_twister(hexdec($seed));
		foreach ($this->rot_mat as $key => $value){
			$this->rot_mat[$key]=$twister->rand(0,3)*90;
			$this->invert_mat[$key]=$twister->rand(0,1);
			#&$this->blocks%2
			if ($key==0) $this->shapes_mat[$key]=$this->rotatable[$twister->array_rand($this->rotatable)];
			else $this->shapes_mat[$key]=$twister->array_rand($this->shapes);
		}
		$forecolors=array($twister->rand($this->identicon_options['forer'][0],$this->identicon_options['forer'][1]), $twister->rand($this->identicon_options['foreg'][0],$this->identicon_options['foreg'][1]), $twister->rand($this->identicon_options['foreb'][0],$this->identicon_options['foreb'][1]));
		$this->colors[1]=imagecolorallocate($this->im, $forecolors[0],$forecolors[1],$forecolors[2]);
		if (array_sum($this->identicon_options['backr']) + array_sum($this->identicon_options['backg']) + array_sum($this->identicon_options['backb'])==0) {
			$this->colors[0]=imagecolorallocatealpha($this->im,0,0,0,127);
			$this->transparent=true;
			imagealphablending ($this->im,false);
			imagesavealpha($this->im,true);
		} else {
			$backcolors=array($twister->rand($this->identicon_options['backr'][0],$this->identicon_options['backr'][1]), $twister->rand($this->identicon_options['backg'][0],$this->identicon_options['backg'][1]), $twister->rand($this->identicon_options['backb'][0],$this->identicon_options['backb'][1]));
			$this->colors[0]=imagecolorallocate($this->im, $backcolors[0],$backcolors[1],$backcolors[2]);
		}
		if($this->identicon_options['grey']){
			$this->colors[1]=imagecolorallocate($this->im, $forecolors[0],$forecolors[0],$forecolors[0]);
			if(!$this->transparent) $this->colors[0]=imagecolorallocate($this->im, $backcolors[0],$backcolors[0],$backcolors[0]);
		}
		return true;
	}

	function identicon_build($seed='',$altImgText='',$img=true,$outsize='',$write=true,$random=true,$displaysize='',$gravataron=true){
		//make an identicon and return the filepath or if write=false return picture directly
		if (function_exists("gd_info")){
			// init random seed
			if ($random) $id=substr(sha1($seed),0,10);
			else $id=$seed;
			$filename=substr(sha1($id.substr(get_option('admin_email'),0,5)),0,15).'.png';
			if ($outsize=='') $outsize=$this->identicon_options['size'];
			if($displaysize=='') $displaysize=$outsize;
			if (!file_exists(WP_IDENTICON_DIR_INTERNAL.$filename)){
				$this->im = imagecreatetruecolor($this->size,$this->size);	
				$this->colors = array(imagecolorallocate($this->im, 255,255,255));
				if ($random) $this->identicon_set_randomness($id);
				else {$this->colors = array(imagecolorallocate($this->im, 255,255,255),imagecolorallocate($this->im, 0,0,0));$this->transparent=false;};
				imagefill($this->im,0,0,$this->colors[0]);
				for ($i=0;$i<$this->blocks;$i++){
					for ($j=0;$j<$this->blocks;$j++){
					$this->identicon_draw_shape($i,$j);
					}
				}

				$out = @imagecreatetruecolor($outsize,$outsize);
				imagesavealpha($out,true);
				imagealphablending($out,false);
				imagecopyresampled($out,$this->im,0,0,0,0,$outsize,$outsize,$this->size,$this->size);
				imagedestroy($this->im);
				if ($write){
						$wrote=imagepng($out,WP_IDENTICON_DIR_INTERNAL.$filename);
						if(!$wrote) return false; //something went wrong but don't want to mess up blog layout
				}else{
					header ("Content-type: image/png");
					imagepng($out);
				}
				imagedestroy($out);
			}
			$filename=get_option('siteurl').WP_IDENTICON_DIR.$filename;
			if($this->identicon_options['gravatar']&&$gravataron)
        $filename = "http://www.gravatar.com/avatar.php?gravatar_id=".md5($seed)."&amp;size=$outsize&amp;default=$filename";
			if ($img){
				$filename='<img class="identicon" src="'.$filename.'" alt="'.str_replace('"',"'",$altImgText).' Identicon Icon" height="'.$displaysize.'" width="'.$displaysize.'" />';
			}
			return $filename;
		} else { //php GD image manipulation is required
			return false; //php GD image isn't installed but don't want to mess up blog layout
		}
	}

	function identicon_display_parts(){
		$this->identicon(1);
		for ($i=0;$i<count($this->shapes);$i++){
			$this->shapes_mat=array($i);
			$this->invert_mat=array(1);
			$output.=$this->identicon_build($seed='example'.$i,$altImgText='',$img=true,$outsize=30,$write=true,$random=false);
			$counter++;
		}
		$this->identicon();
	return $output;
	}
}

//create identicon for later use
global $identicon;
$identicon = new identicon;

function identicon_get_options(){
	$identicon_array=get_option('identicon');
	if (!isset($identicon_array['size'])|!isset($identicon_array['backb'])){
		//Set Default Values Here
		$default_array=array('size'=>35,'backr'=>array(255,255),'backg'=>array(255,255),'backb'=>array(255,255), 'forer'=>array(1,255),'foreg'=>array(1,255),'foreb'=>array(1,255),'squares'=>4,'autoadd'=>1,'gravatar'=>0,'grey'=>0);
		add_option('identicon',$default_array,'Options used by Identicon',false);
		$identicon_array=$default_array;
	}
	return($identicon_array);
}

function identicon_subpanel() {
	echo "<div class='wrap'>";
	if (isset($_POST['submit'])) { //update the identicon size option
		$identicon_options=identicon_get_options();
		$identiconsize=intval($_POST['identiconsize']);
		$identiconsquares=intval($_POST['identiconsquares']);
		if ($identiconsize > 0 & $identiconsize < 400){
			$identicon_options['size']=$identiconsize;
		}else{
			echo "<div class='error'><p>Please enter an integer for size. Preferably between 30-200.</p></div>";		
		}
		if ($identiconsquares > 0){
			$identicon_options['squares']=$identiconsquares;
		}else{
			echo "<div class='error'><p>Please enter an integer for squares. Preferably 3 or greater and probably less than 10.".$identiconsquares.$_POST['identiconsquares']."</p></div>";		
		}
		foreach(array('backr','backg','backb','forer','foreg','foreb') as $color){//update background color options
			$colorarray=explode('-',$_POST[$color]);
			if (count($colorarray)==1){
				$colorarray[1]=$colorarray[0];
			}
			$colorarray[0]=intval($colorarray[0]);
			$colorarray[1]=intval($colorarray[1]);
			if ($colorarray[0]>=0 & $colorarray[0]<256 & $colorarray[1]>=0 & $colorarray[1]<256){
				$identicon_options[$color]=$colorarray;
			}else{
				echo "<div class='error'><p>Please enter a range between two integers for the background color (e.g. 230-255) between 1 and 255. For a single color please enter a single value (e.g. white = 255 for r,g and b).</p></div>";		
			}
		}
		if ($_POST['autoadd'] == 0) $identicon_options['autoadd']=0;
		elseif ($_POST['autoadd'] == 1) $identicon_options['autoadd']=1;
		elseif ($_POST['autoadd'] == 2) $identicon_options['autoadd']=2;
		if ($_POST['gravatar'] == 0) $identicon_options['gravatar']=0;
		elseif ($_POST['gravatar'] == 1) $identicon_options['gravatar']=1;
		if ($_POST['grey'] == 1) $identicon_options['grey']=1;
		else $identicon_options['grey']=0;
		update_option('identicon', $identicon_options);
		echo "<div class='updated'><p>Options updated (you may have to clear the identicon cache to see any effect).</p></div>";
	}elseif (isset($_POST['clear'])){ //clear the identicon cache
		$dir=WP_IDENTICON_DIR_INTERNAL;
		if ($dh = opendir($dir)) {
			while (($file = readdir($dh)) !== false) {
				if (is_file($dir.$file) and preg_match('/^.*\.png$/',$file)){
					unlink($dir.$file);
				}
			}
		closedir($dh);
		echo "<div class='updated'><p>Cache cleared.</p></div>";		
		}
	}
	$identicon_options=identicon_get_options();
	//count file
	$identicon_count=0;
	$dir=WP_IDENTICON_DIR_INTERNAL;
	if ($dh = opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			if (is_file($dir.$file) and preg_match('/^[a-f0-9]*\.png$/',$file)){
				$identicon_count++;
			}
		}
	}
	?>
	<div><p><strong>This is the Identicon options page.</strong></p>
	<p>You currently have <?php echo $identicon_count;?> identicons on your website.</p>
	</div>
	<div class='wrap'>
	<p>Set options here:</p>
	<form method="post" action="options-general.php?page=wp_identicon.php">
		<ul style="list-style-type: none">
	<li><strong>Identicon Size in Pixels</strong> (Default: 35):<br /> 
		<input type="text" name="identiconsize" value="<?php echo $identicon_options['size'];?>"/></li>
	<li><strong>Number of Squares</strong> (Default: 4):<br /> 
		<input type="text" name="identiconsquares" value="<?php echo $identicon_options['squares'];?>"/></li>
	<?php if(!$identicon_options['grey']){
		$gbtype="text";
		$input_back=array('r'=>'style="background:#fee;"','g'=>'style="background:#efe;"','b'=>'style="background:#eef;"');
		$input_detail=array('r'=>'R:','g'=>'G:','b'=>'B:');
	}else{
		$gbtype="hidden";
		$input_back=array('r'=>'','g'=>'','b'=>'');
		$input_detail=array('r'=>'Gray:','g'=>'','b'=>'');
	}?>
	<li><strong>Background Colors</strong> (enter single value or range. 0,0,0 for transparent. Default: 255,255,255):<br />
	<?php echo $input_detail['r'];?><input type="text" name="backr" <?php echo $input_back['r'];?> value="<?php echo implode($identicon_options['backr'],'-');?>"/>
	<?php echo $input_detail['g'];?><input type="<?php echo $gbtype;?>" name="backg" <?php echo $input_back['g'];?> value="<?php echo implode($identicon_options['backg'],'-');?>"/>
	<?php echo $input_detail['b'];?><input type="<?php echo $gbtype;?>" name="backb" <?php echo $input_back['b'];?> value="<?php echo implode($identicon_options['backb'],'-');?>"/></li>
	<li><strong>Foreground Colors</strong> (enter single value or range. Default: 1-255,1-255,1-255):<br/>
	<?php echo $input_detail['r'];?><input type="text" name="forer" <?php echo $input_back['r'];?> value="<?php echo implode($identicon_options['forer'],'-');?>"/>
	<?php echo $input_detail['g'];?><input type="<?php echo $gbtype;?>" name="foreg" <?php echo $input_back['g'];?> value="<?php echo implode($identicon_options['foreg'],'-');?>"/>
	<?php echo $input_detail['b'];?><input type="<?php echo $gbtype;?>" name="foreb" <?php echo $input_back['b'];?> value="<?php echo implode($identicon_options['foreb'],'-');?>"/></li>
	<li><strong>Grayscale</strong> (Good for black and white themes):<input type="checkbox" name="grey" value="1" <?php if ($identicon_options['grey']) echo 'checked="checked"';?> /></li>
	<li><strong>Automatically Add Identicons to Comments</strong> (adds an Identicon beside commenter names or disable it and edit theme file manually) (default: Auto)<br /> <input type="radio" name="autoadd" value="0" <?php if (!$identicon_options['autoadd']) echo 'checked="checked"';?>/> I'll Do It Myself <input type="radio" name="autoadd" value="1" <?php if ($identicon_options['autoadd']==1) echo 'checked="checked"';?>/> Add Identicons For Me <input type="radio" name="autoadd" value="2" <?php if ($identicon_options['autoadd']==2) echo 'checked="checked"';?>/> My Theme Has Builtin WP2.5+ Avatars</li>
	<li><strong>Gravatar Support</strong> (If a commenter has a gravatar use it, otherwise use Identicon) (default: Identicon Only)<br /> <input type="radio" name="gravatar" value="0" <?php if (!$identicon_options['gravatar']) echo 'checked="checked"';?>/> Identicon Only <input type="radio" name="gravatar" value="1" <?php if ($identicon_options['gravatar']) echo 'checked="checked"';?>/> Gravatar + Identicon</li>
	<li><input type="submit" name="submit" value="Set Options"/></li>
	</ul>
	</form>
	<form method="post" action="options-general.php?page=wp_identicon.php">
	<ul style="list-style-type: none"><li>Clear the Identicon Image Cache: <input type="submit" name="clear" value="Clear Cache"/></li></ul>
	</form>
	</div>
	<div class='wrap'><h4>To use Identicon:</h4> <p>Make sure the folder <code>wp-content/plugins/identicon</code> is writable. Identicons should automatically be added beside your commentors names after that. Enjoy.</p> 
	<p>If you use the Recent Comments Widget in your sidebar, this plugin also provides a replacement Recent Comments (with Identicons) Widget to add Identicons to the sidebar comments (just set it in the Widgets Control Panel)</p>
	<strong>Testing:</strong><br/>
	<?php if (!is_writable(''.WP_IDENTICON_DIR_INTERNAL)){echo "<div class='error'><p>Identicon needs ".WP_IDENTICON_DIR_INTERNAL." to be writable.</p></div>";}
	if (!function_exists("gd_info")){echo "<div class='error'><p>GD Image library not found. Identicon needs this library.</p></div>";}?>
	<p>A test identicon should be here:<?php $identicon=new identicon; echo $identicon->identicon_build('This is a test','Test');?> and the source URL for this image is <a href="<?php echo $identicon->identicon_build('This is a test','Test',false);?>">here</a>.</p>
	<p>If there is no identicon above or there are any other problems, concerns or suggestions please let me know <a href="http://scott.sherrillmix.com/blog/blogger/wp_identicon">here</a>. Enjoy your identicons.</p></div>
	<div class="wrap"><p>For curiosity's sake, here are the parts the identicons are built from:</p><div class='wrap'>
	<?php echo $identicon->identicon_display_parts();?>
	</div>
	<h4>For advanced users:</h4> <p>Disable the automatic Indenticon placement and put: <br/> <code><?php echo htmlspecialchars('<?php if (function_exists("identicon_build")) {echo identicon_build($comment->comment_author_email,$comment->comment_author); } ?>');?></code><br/> in the comment loop of your theme comment script (probably <code>comments.php</code>). Or if you're more confident and just want the img URL use:
	<code><?php echo htmlspecialchars('<?php if (function_exists("identicon_build")) {echo identicon_build($comment->comment_author_email,$comment->comment_author,false); } ?>');?></code></p>
	<p>Please see the <a href="http://scott.sherrillmix.com/blog/blogger/wp_identicon/">plugin page</a> if you need more details.</p></div>
	

	<div><p>The idea for Identicons came from <a href="http://www.docuverse.com/blog/donpark/2007/01/18/visual-security-9-block-ip-identification">Don Park</a>.</p></div>
	</div>
	<?php	
}


class identicon_mersenne_twister{
//Copied from wikipedia pseudocode
//Don't call over 600 times (without recalling the constructor)
// Create a length 624 array to store the state of the generator
 var $MT;
 var $i;
 // Initialise the generator from a seed
 function identicon_mersenne_twister ($seed=123456) {
     $this->MT[0] = $seed;
		 $this->i=1;
     for ($i=1;$i<624;$i++) { // loop over each other element
         $this->MT[$i] = $this->mysql_math('(1812433253 * ('.$this->MT[$i-1].' ^ ('.$this->MT[$i-1]." >> 30)) + $i) & 0xffffffff");
     }
		 $this->generateNumbers();
 }

	//(some) PHP integers don't have enough bits for Mersenne Twister so use mysql
	function mysql_math($equation){
		global $wpdb;
		$query="SELECT ".$equation;
		$answer=$wpdb->get_var($query);
		return $answer;
	}

 // Generate an array of 624 untempered numbers
 function generateNumbers() {
     for ($i=0;$i<624;$i++) {
         $y = $this->mysql_math('('.$this->MT[$i].' & 0x7fffffff) + ('.$this->MT[($i+1)%624].' & 0xfffffffe)');
				 $even=$this->mysql_math($y.' ^ 0x00000001');
         if ($even) {
             $this->MT[$i] = $this->mysql_math($this->MT[($i + 397) % 624]." ^ ($y >> 1)");
         } else {
             $this->MT[$i] = $this->mysql_math($this->MT[($i + 397) % 624]." ^ ($y >>1) ^ (2567483615)"); // 0x9908b0df
         }
     }
 }
 
 // Extract a tempered pseudorandom number based on the i-th value
 // generateNumbers() will have to be called again once the array of 624 numbers is exhausted
 function extractNumber() {
     $y = $this->MT[$this->i];
     $y = $this->mysql_math("$y ^ ($y >>11) ^ (($y << 7) & 2636928640) ^ (($y << 15) & 4022730752) ^ ($y >>18)");
		 $this->i++;
     return $y/0xffffffff;
 }

	function rand($low,$high){
		$pick=floor($low+($high-$low+1)*$this->extractNumber());
		return ($pick);
	}
	function array_rand($array){
		return($this->rand(0,count($array)-1));
	}
}


function identicon_comment_author($output){
		global $comment;
		global $identicon;
		$identicon_options=identicon_get_options();
		if((is_page () || is_single ()) && $identicon_options['autoadd']==1 && $comment->comment_type!="pingback"&&$comment->comment_type!="trackback" && isset($comment->comment_karma)){ //assuming sidebar widgets won't check comment karma (and single page comments will)
			if (isset($identicon)) $output=$identicon->identicon_build($comment->comment_author_email,$comment->comment_author).' '.$output; 
		}
		return $output;
}

function identicon_build($seed='',$altImgText='',$img=true,$outsize='',$write=true,$random=true,$displaysize='',$gravataron=true){
	global $identicon;
	return $identicon->identicon_build($seed,$altImgText,$img,$outsize,$write,$random,$displaysize,$gravataron);
}

function identicon_get_avatar($avatar, $id_or_email, $size, $default){
	global $identicon;
	if(!isset($identicon)) return $avatar;
	$email = '';
	if ( is_numeric($id_or_email) ) {
		$id = (int) $id_or_email;
		$user = get_userdata($id);
		if ( $user )
			$email = $user->user_email;
	} elseif ( is_object($id_or_email) ) {
		if ( !empty($id_or_email->user_id) ) {
			$id = (int) $id_or_email->user_id;
			$user = get_userdata($id);
			if ( $user)
				$email = $user->user_email;
		} elseif ( !empty($id_or_email->comment_author_email) ) {
			$email = $id_or_email->comment_author_email;
		}
	} else {
		$email = $id_or_email;
	}

	if(!$avatar) return identicon_build($email,'','',true,$size);
	if(!$identicon->identicon_options['gravatar']){
		$identiconurl=identicon_build($email,'',false);
		$newavatar=preg_replace('@src=(["\'])http://[^"\']+["\']@','src=\1'.$identiconurl.'\1',$avatar);
		$avatar=$newavatar;
	}elseif($identicon->identicon_options['gravatar']==1){
		$identiconurl=identicon_build($email,'',false,'',true,true,$size,false);
		if(strpos($avatar,'default=http://')!==false){
			$newavatar=preg_replace('@default=http://[^&\'"]+([&\'"])@','default='.urlencode($identiconurl).'\1',$avatar);
		}else{
			$newavatar=preg_replace('@(src=(["\'])http://[^?]+\?)@','\1default='.urlencode($identiconurl).'&amp;',$avatar);
		}
		$avatar=$newavatar;
	}
	return($avatar);
}

//Hooks
add_action('admin_menu', 'identicon_menu');
add_filter('get_comment_author','identicon_comment_author');
if($wp_version>=2.5&&$identicon->identicon_options['autoadd']==2){
	add_filter('get_avatar','identicon_get_avatar',5,4);
}

//Widget stuff 
//Wordpress's default widget doesn't get commenter email so we can't use it for identicons
//Copying their widget with some search and replace with identicon
function identicon_recent_comments($args) {
	global $wpdb, $comments, $comment, $identicon;
	extract($args, EXTR_SKIP);
	$options = get_option('widget_identicon_recent_comments');
	$title = empty($options['title']) ? __('Recent Comments') : $options['title'];
	if ( !$number = (int) $options['number'] )
		$number = 5;
	else if ( $number < 1 )
		$number = 1;
	else if ( $number > 15 )
		$number = 15;
	if ( !$size = (int) $options['identicon_size'] )
		$size = 10;
	else if ( $size < 1 )
		$size=1;
	else if($size > 50)
		$size=50;
	if ( !$comments = wp_cache_get( 'identicon_recent_comments', 'widget' ) ) {
		$comments = $wpdb->get_results("SELECT comment_author, comment_author_url, comment_ID, comment_post_ID, comment_author_email, comment_type FROM $wpdb->comments WHERE comment_approved = '1' ORDER BY comment_date_gmt DESC LIMIT $number");
		wp_cache_add( 'identicon_recent_comments', $comments, 'widget' );
	}
?>

		<?php echo $before_widget; ?>
			<?php echo $before_title . $title . $after_title; ?>
			<ul id="identicon_recentcomments"><?php
			if ( $comments ) : foreach ($comments as $comment) :
				echo  '<li class="recentcomments">';
				if($comment->comment_type!="pingback"&&$comment->comment_type!="trackback"&&isset($identicon))
					echo $identicon->identicon_build($comment->comment_author_email,$comment->comment_author,TRUE,'',TRUE,TRUE,$size).' ';
				echo sprintf(__('%1$s on %2$s'), get_comment_author_link(), '<a href="'. get_permalink($comment->comment_post_ID) . '#comment-' . $comment->comment_ID . '">' . get_the_title($comment->comment_post_ID) . '</a>') . '</li>';
			endforeach; endif;?></ul>
		<?php echo $after_widget; ?>
<?php
}

function wp_delete_identicon_recent_comments_cache() {
	wp_cache_delete( 'identicon_recent_comments', 'widget' );
}

function identicon_recent_comments_control() {
	$options = $newoptions = get_option('widget_identicon_recent_comments');
	if ( $_POST["identicon_recent-comments-submit"] ) {
		$newoptions['title'] = strip_tags(stripslashes($_POST["identicon_recent-comments-title"]));
		$newoptions['number'] = (int) $_POST["identicon_recent-comments-number"];
		$newoptions['identicon_size'] = (int) $_POST["identicon_size"];
	}
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_identicon_recent_comments', $options);
		wp_delete_identicon_recent_comments_cache();
	}
	$title = attribute_escape($options['title']);
	if ( !$number = (int) $options['number'] )
		$number = 5;
	if ( !$size = (int) $options['identicon_size'] )
		$size = 10;
?>
			<p><label for="identicon_recent-comments-title"><?php _e('Title:'); ?> <input style="width: 250px;" id="identicon_recent-comments-title" name="identicon_recent-comments-title" type="text" value="<?php echo $title; ?>" /></label></p>
			<p><label for="identicon_recent-comments-number"><?php _e('Number of comments to show:'); ?> <input style="width: 25px; text-align: center;" id="identicon_recent-comments-number" name="identicon_recent-comments-number" type="text" value="<?php echo $number; ?>" /></label> <?php _e('(at most 15)'); ?></p>
			<p><label for="identicon_size"><?php _e('Size of Widget Identicons (pixels):'); ?> <input style="width: 25px; text-align: center;" id="identicon_size" name="identicon_size" type="text" value="<?php echo $size; ?>" /></label></p>
			<input type="hidden" id="identicon_recent-comments-submit" name="identicon_recent-comments-submit" value="1" />
<?php
}

function identicon_recent_comments_style() {
?>
<style type="text/css">
	ul#identicon_recentcomments{list-style:none;} 
	ul#identicon_recentcomments li.recentcomments:before{content:"";}
	ul#identicon_recentcomments img.identicon{vertical-align:middle;}
	.recentcomments a{display:inline !important;padding: 0 !important;margin: 0 !important;}
</style>
<?php
}

function identicon_recent_comments_widget_init(){
	register_sidebar_widget('Recent Comments (with Identicons)', 'identicon_recent_comments');
	register_widget_control('Recent Comments (with Identicons)', 'identicon_recent_comments_control', 320, 90);
	if ( is_active_widget('identicon_recent_comments') )
		add_action('wp_head', 'identicon_recent_comments_style');
	add_action( 'comment_post', 'wp_delete_identicon_recent_comments_cache' );
	add_action( 'wp_set_comment_status', 'wp_delete_identicon_recent_comments_cache' );
}

add_action('widgets_init', 'identicon_recent_comments_widget_init');
?>