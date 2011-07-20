<?php

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Software: mPDF, Unicode-HTML Free PDF generator						*
 * Version:  X.0		   based on										*
 *		   FPDF by Olivier PLATHEY										*
 *		   HTML2FPDF by Renato Coelho									*
 * Date:	 2010-09-19													*
 * Author:   Ian Back <ianb@bpm1.com>									*
 * Author:   Carl Holmberg <info@talgdank.se>							*
 * License:  GPL														*
 *																		*
 * Changes:  See changelog.txt											*
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */


class Color
{
	static function lighten($c)
	{
		if (isset($c['R'])) {
			die('Color error in Color::lighten()');
		}
		if ($c[0]==3 || $c[0]==5) {  // RGB
			list($h, $s, $l) = Color::rgb2hsl($c[1]/255, $c[2]/255, $c[3]/255);
			$l += ((1 - $l)*0.8);
			list($r, $g, $b) = Color::hsl2rgb($h, $s, $l);
			return array(3, $r, $g, $b);
		} else if ($c[0]==4 || $c[0]==6) { 	// CMYK
			return array(4, max(0, $c[1]-20), max(0, $c[2]-20), max(0, $c[3]-20), max(0, $c[4]-20));
		} else if ($c[0]==1) {	// Grayscale
			return array(1, min(255, $c[1]+32));
		}
		return $c;
	}
	
	
	static function darken($c)
	{
		if (isset($c['R'])) {
			die('Color error in Color::darken()');
		}
		if ($c[0]==3 || $c[0]==5) {  // RGB  
			list($h, $s, $l) = Color::rgb2hsl($c[1]/255, $c[2]/255, $c[3]/255);
			list($r, $g, $b) = Color::hsl2rgb($h, $s*0.25, $l*0,75);
			return array(3, $r, $g, $b);
		} else if ($c[0]==4 || $c[0]==6) {  // CMYK
			return array(4, min(100, $c[1]+20), min(100, $c[2]+20), min(100, $c[3]+20), min(100, $c[4]+20));
		} else if ($c[0]==1) {  // Grayscale
			return array(1, max(0, $c[1]-32));
		}
		return $c;
	}
	
	static function rgb2gray($c)
	{
		if (isset($c[4])) {
			return array(1,($c[1] * .21 + $c[2] * .71 + $c[3] * .07), $c[4]);
		} else {
			return array(1,($c[1] * .21 + $c[2] * .71 + $c[3] * .07));
		}
	}
	
	static function cmyk2gray($c)
	{
		return self::rgb2gray(self::cmyk2rgb($c));
	}

	static function rgb2cmyk($c)
	{
		$cyan = 1 - $c[1] / 255;
		$magenta = 1 - $c[2] / 255;
		$yellow = 1 - $c[3] / 255;
		$K = min($cyan, $magenta, $yellow);

		if ($K == 1) {
			if ($c[0] == 5) {
				return array (6, 100, 100, 100, 100, $c[4]);
			} else {
				return array (4, 100, 100, 100, 100);
			}
		}
		$black = 100/(1 - $K);
		if ($c[0] == 5) {
			return array (6,($cyan-$K)*$black, ($magenta-$K)*$black, ($yellow-$K)*$black, $K*100, $c[4]);
		} else {
			return array (4,($cyan-$K)*$black, ($magenta-$K)*$black, ($yellow-$K)*$black, $K*100);
		}
	}
	
	
	static function cmyk2rgb($c)
	{
		$colors = 255 - ($c[4]*2.55);
		$r = intval($colors * (1 - $c[1]*0.01));
		$g = intval($colors * (1 - $c[2]*0.01));
		$b = intval($colors * (1 - $c[3]*0.01));
		if ($c[0] == 6) {
			return array (5, $r, $g, $b, $c[5]);
		} else {
			return array (3, $r, $g, $b);
		}
	}
	
	static function rgb2hsl($var_r, $var_g, $var_b)
	{
		$var_min = min($var_r,$var_g,$var_b);
		$var_max = max($var_r,$var_g,$var_b);
		$del_max = $var_max - $var_min;
		$l = ($var_max + $var_min) / 2;
		if ($del_max == 0) {
				$h = 0;
				$s = 0;
		}
		else {
				if ($l < 0.5) { $s = $del_max / ($var_max + $var_min); }
				else { $s = $del_max / (2 - $var_max - $var_min); }
				$del_r = ((($var_max - $var_r) / 6) + ($del_max / 2)) / $del_max;
				$del_g = ((($var_max - $var_g) / 6) + ($del_max / 2)) / $del_max;
				$del_b = ((($var_max - $var_b) / 6) + ($del_max / 2)) / $del_max;
				if ($var_r == $var_max) { $h = $del_b - $del_g; }
				elseif ($var_g == $var_max)  { $h = (1 / 3) + $del_r - $del_b; }
				elseif ($var_b == $var_max)  { $h = (2 / 3) + $del_g - $del_r; };
				if ($h < 0) { $h += 1; }
				if ($h > 1) { $h -= 1; }
		}
		return array($h,$s,$l);
	}
	
	
	function hsl2rgb($h2,$s2,$l2)
	{
		// Input is HSL value of complementary colour, held in $h2, $s, $l as fractions of 1
		// Output is RGB in normal 255 255 255 format, held in $r, $g, $b
		// Hue is converted using function hue2rgb, shown at the end of this code
		if ($s2 == 0) {
			$r = $l2 * 255;
			$g = $l2 * 255;
			$b = $l2 * 255;
		}
		else {
			if ($l2 < 0.5) { $var_2 = $l2 * (1 + $s2); }
			else { $var_2 = ($l2 + $s2) - ($s2 * $l2); }
			$var_1 = 2 * $l2 - $var_2;
			$r = round(255 * self::hue2rgb($var_1,$var_2,$h2 + (1 / 3)));
			$g = round(255 * self::hue2rgb($var_1,$var_2,$h2));
			$b = round(255 * self::hue2rgb($var_1,$var_2,$h2 - (1 / 3)));
		}
		return array($r,$g,$b);
	}
	
	static function hue2rgb($v1,$v2,$vh)
	{
		// Function to convert hue to RGB, called from above
		if ($vh < 0) { $vh += 1; };
		if ($vh > 1) { $vh -= 1; };
		if ((6 * $vh) < 1) { return ($v1 + ($v2 - $v1) * 6 * $vh); };
		if ((2 * $vh) < 1) { return ($v2); };
		if ((3 * $vh) < 2) { return ($v1 + ($v2 - $v1) * ((2 / 3 - $vh) * 6)); };
		return ($v1);
	}
	
	static function invert($cor)
	{
		if ($cor[0]==3 || $cor[0]==5) {	// RGB
			return array(3, (255-$cor[1]), (255-$cor[2]), (255-$cor[3]));
		}
		else if ($cor[0]==4 || $cor[0]==6) {	// CMYK
			return array(4, (100-$cor[1]), (100-$cor[2]), (100-$cor[3]), (100-$cor[4]));
		}
		else if ($cor[0]==1) {	// Grayscale
			return array(1, (255-$cor[1]));
		}	
		// Cannot cope with non-RGB colors at present
		die('Error in _invertColor - trying to invert non-RGB color');
	}
	
	static function array2string($cor)
	{
		$s = '';
		if ($cor[0]==1) $s = 'rgb('.$cor[1].','.$cor[1].','.$cor[1].')';
		else if ($cor[0]==2) $s = 'spot('.$cor[1].','.$cor[2].')';		// SPOT COLOR
		else if ($cor[0]==3) $s = 'rgb('.$cor[1].','.$cor[2].','.$cor[3].')';
		else if ($cor[0]==4) $s = 'cmyk('.$cor[1].','.$cor[2].','.$cor[3].','.$cor[4].')';
		else if ($cor[0]==5) $s = 'rgba('.$cor[1].','.$cor[2].','.$cor[3].','.$cor[4].')';
		else if ($cor[0]==6) $s = 'cmyka('.$cor[1].','.$cor[2].','.$cor[3].','.$cor[4].','.$cor[5].')';
		return $s;
	}
}

class Text
{
	static function getCharWidth(&$cw, $u, $isdef=true)
	{
		global $ords;
		if ($u == 0) {
			$w = false;
		} else {
			$w = ($ords[$cw[$u*2]] << 8) + $ords[$cw[$u*2+1]];
		}
		if ($w == 65535) {
			return 0;
		} else if ($w) {
			return $w;
		} else if ($isdef) {
			return false;
		} else {
			return 0;
		}
	}
	
	static function charDefined(&$cw, $u) {
		global $ords;
		if ($u == 0) {
			return false;
		}
		$w = ($ords[$cw[$u*2]] << 8) + $ords[$cw[$u*2+1]];
		return $w? true : false;
	}
}
