<?php

use SVG\SVG;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Shapes\SVGCircle;
use SVG\Nodes\Shapes\SVGPolygon;
use SVG\Nodes\Shapes\SVGPath;
use SVG\Nodes\Texts\SVGText;
use SVG\Nodes\Texts\SVGTitle;
use SVG\Nodes\Structures\SVGGroup;
use SVG\Nodes\Structures\SVGDocumentFragment;


class VehicleSvg{
	
	const SIZE_FOOTNOTE_HORIZONTAL = 0;
	const SIZE_FOOTNOTE_VERTICAL = 1;
	private $trailer;
	private $semi_trailer;
	private $row_truck;
	private $svg;
	private $svgdocument;
	
	
	public function __construct($width, $height)
	{
		$this->svg = new SVG($width, $height);
		$this->svgdocument = $this->svg->getDocument();
	}
	
	public function setVehicleXml(SimpleXMLElement $vehicle)
	{
		switch ((string)$vehicle['kind']) {
			case 'trailer':
				$this->addTrailerXml($vehicle);
			break;
			case 'semi_trailer':
				$this->addSemiTrailerXml($vehicle);
			break;
			case 'row_truck':
				$this->addRowTruckXml($vehicle);
			break;
			default:
				return new Exception("unknown kind");
		}
	}
	
	public function addTrailerXml(SimpleXMLElement $trailer)
	{
		if ((string)$trailer['kind']!='trailer') return new Exception("incorrect kind");
		$this->trailer = [
			'full_length' => (string)$trailer->full_length ? (string)$trailer->full_length : (string)$trailer->length,
			'full_width' => (string)$trailer->full_width ? (string)$trailer->full_width : (string)$trailer->width,
			'full_height' => (string)$trailer->full_height ? (string)$trailer->full_height : (string)$trailer->height,
			'length' => (string)$trailer->length,
			'width' => (string)$trailer->width,
			'height' => (string)$trailer->height,
			'axle_front' => [
				'front_distance' => (string)$trailer->axle_front->front_distance,
				'num_of_wheels' => (string)$trailer->axle_front->num_of_wheels,
				'wheel_distance' => (string)$trailer->axle_front->wheel_distance ? (string)$trailer->axle_front->wheel_distance : 1000,
				'max_tonnage' => (string)$trailer->axle_front->max_tonnage,
			],
			'axle_back' => [
				'back_distance' => (string)$trailer->axle_back->back_distance,
				'num_of_wheels' => (string)$trailer->axle_back->num_of_wheels,
				'wheel_distance' => (string)$trailer->axle_back->wheel_distance ? (string)$trailer->axle_back->wheel_distance : 1000,
				'max_tonnage' => (string)$trailer->axle_back->max_tonnage,
			],
		];
	}
	
	public function addSemiTrailerXml(SimpleXMLElement $trailer)
	{
		if ((string)$trailer['kind']!='semi_trailer') return new Exception("incorrect kind");
		$this->semi_trailer = [
			'full_length' => (string)$trailer->full_length,
			'full_width' => (string)$trailer->full_width,
			'full_height' => (string)$trailer->full_height,
			'length' => (string)$trailer->length,
			'width' => (string)$trailer->width,
			'height' => (string)$trailer->height,
			'axle_back' => [
				'back_distance' => (string)$trailer->axle_back->back_distance,
				'num_of_wheels' => (string)$trailer->axle_back->num_of_wheels,
				'wheel_distance' => (string)$trailer->axle_back->wheel_distance ? (string)$trailer->axle_back->wheel_distance : 1000,
				'max_tonnage' => (string)$trailer->axle_back->max_tonnage,
			],
			'anchor_front_distance' => (string)$trailer->anchor->front_distance,
		];
	}
	
	public function addRowTruckXml(SimpleXMLElement $truck)
	{
		if ((string)$truck['kind']!='row_truck') return new Exception("incorrect kind");
		$this->row_truck = [
			'full_length' => (string)$truck->full_length,
			'axle_front' => [
				'front_distance' => (string)$truck->axle_front->front_distance,
				'num_of_wheels' => (string)$truck->axle_front->num_of_wheels,
				'wheel_distance' => (string)$truck->axle_front->wheel_distance ? (string)$truck->axle_front->wheel_distance : 1000,
				'max_tonnage' => (string)$truck->axle_front->max_tonnage,
			],
			'axle_back' => [
				'back_distance' => (string)$truck->axle_back->back_distance,
				'num_of_wheels' => (string)$truck->axle_back->num_of_wheels,
				'wheel_distance' => (string)$truck->axle_back->wheel_distance ? (string)$truck->axle_back->wheel_distance : 1000,
				'max_tonnage' => (string)$truck->axle_back->max_tonnage,
			],
			'anchor_front_distance' => (string)$truck->anchor->front_distance,
			'anchor_height' => (string)$truck->anchor->anchor_height,
		];
	}
	
	public function generateSvg()
	{
		if ($this->semi_trailer || $this->row_truck) {
			if (!$this->row_truck || !$this->semi_trailer) new Exception("row_truck and semi_trailer can be generated only in pair");
			
			$footnoteArrorSize = 4;
			$footnoteLineSize = 2;
			$footnoteFontSize = 18;
			$footnoteOffset = 25;
			$footnoteConvas = 100;
			$tonnageConvas = 100;
			$clearance = 1000;
			$paddingSide = 40;
			
			$profileWidth = $this->row_truck['anchor_front_distance'] + $this->semi_trailer['length'] - $this->semi_trailer['anchor_front_distance'];
			$profileHeight = $clearance + $this->semi_trailer['height'];
			$topWidth = $profileWidth;
			$topHeight = $this->semi_trailer['width'];
			$backWidth = $this->semi_trailer['width'];
			$backHeight = $this->semi_trailer['height'] + $clearance;
			$scale = min(($this->svgdocument->getWidth() - $footnoteConvas - $paddingSide) / ($profileWidth + $backWidth),
						($this->svgdocument->getHeight() - $footnoteConvas - $tonnageConvas - $paddingSide) / ($profileHeight + $topHeight));
			
			$topSide = new SVGDocumentFragment;
			$topSide->setAttribute('id', 'TopSide');
			$lowerSide = new SVGDocumentFragment;
			$lowerSide->setAttribute('id', 'LowerSide');
			$lowerSide->setAttribute('y', $topHeight*$scale+$paddingSide);
			$rightLowerSide = new SVGDocumentFragment;
			$rightLowerSide->setAttribute('id', 'RightLowerSide');
			$rightLowerSide->setAttribute('x', $topWidth*$scale+$paddingSide);
			$rightLowerSide->setAttribute('y', $topHeight*$scale+$paddingSide);
			
			// top
			$topSide->addChild(
				(new SVGDocumentFragment)
					->setAttribute('id', 'semiTrailerContainer')
					->addChild($this->drawSemiTrailerTop(
						(new SVGGroup)->setAttribute('transform', "scale({$scale},{$scale})"),
						$topWidth, $topHeight, $scale
					))
					->setAttribute('x', $footnoteConvas)
					// ->setAttribute('y', $footnoteConvas)
			);
			
			// lower
			$lowerSide->addChild(
				(new SVGDocumentFragment)
					->setAttribute('id', 'rowTruckContainer')
					->addChild($this->drawTruckProfile(
						(new SVGGroup)->setAttribute('transform', "scale({$scale},{$scale})"),
						$profileWidth, $profileHeight, $scale
					))
					->setAttribute('x', $footnoteConvas)
					->setAttribute('y', $footnoteConvas)
			);
			$lowerSide->addChild(
				(new SVGDocumentFragment)
					->setAttribute('id', 'semiTrailerContainer')
					->addChild($this->drawSemiTrailerProfile(
						(new SVGGroup)->setAttribute('transform', "scale({$scale},{$scale})"),
						$profileWidth, $profileHeight, $scale
					))
					->setAttribute('x', $footnoteConvas)
					->setAttribute('y', $footnoteConvas)
			);
			
			
			// sizes
			$lowerSide->addChild(
				(new SVGDocumentFragment)
					// full height
					->addChild(
						$this->drawSizeFootnote(
							'Full Height',
							$this->semi_trailer['full_height'],
							$footnoteOffset*2,
							$footnoteConvas,
							($this->semi_trailer['height']+$clearance)*$scale,
							self::SIZE_FOOTNOTE_VERTICAL,
							$footnoteLineSize,
							$footnoteArrorSize,
							$footnoteFontSize
						)
					)
					
					// anchor height
					->addChild(
						$this->drawSizeFootnote(
							'Anchor Height',
							$this->row_truck['anchor_height'],
							$footnoteOffset*3,
							$footnoteConvas + $this->semi_trailer['height']*$scale,
							$clearance*$scale,
							self::SIZE_FOOTNOTE_VERTICAL,
							$footnoteLineSize,
							$footnoteArrorSize,
							$footnoteFontSize
						)
					)
					
					// full length
					->addChild(
						$this->drawSizeFootnote(
							'Full Length',
							$this->row_truck['anchor_front_distance'] + $this->semi_trailer['length'] - $this->semi_trailer['anchor_front_distance'],
							$footnoteConvas,
							$footnoteOffset,
							($this->row_truck['anchor_front_distance'] + $this->semi_trailer['length'] - $this->semi_trailer['anchor_front_distance'])*$scale,
							self::SIZE_FOOTNOTE_HORIZONTAL,
							$footnoteLineSize,
							$footnoteArrorSize,
							$footnoteFontSize
						)
					)
					
					// trailer length
					->addChild(
						$this->drawSizeFootnote(
							'Semi Trailer Length',
							$this->semi_trailer['full_length'],
							$footnoteConvas + ($this->row_truck['anchor_front_distance'] - $this->semi_trailer['anchor_front_distance']) * $scale,
							$footnoteOffset*2,
							$this->semi_trailer['length']*$scale,
							self::SIZE_FOOTNOTE_HORIZONTAL,
							$footnoteLineSize,
							$footnoteArrorSize,
							$footnoteFontSize
						)
					)		
					
					// axle back distance
					->addChild(
						$this->drawSizeFootnote(
							'Axle Back Distance',
							$this->semi_trailer['axle_back']['back_distance'],
							$footnoteConvas + ($this->row_truck['anchor_front_distance'] + $this->semi_trailer['length'] - $this->semi_trailer['anchor_front_distance'] - $this->semi_trailer['axle_back']['back_distance']) * $scale,
							$footnoteOffset*3,
							$this->semi_trailer['axle_back']['back_distance']*$scale,
							self::SIZE_FOOTNOTE_HORIZONTAL,
							$footnoteLineSize,
							$footnoteArrorSize,
							$footnoteFontSize
						)
					)
			);
			
			// tonnage
			$tonnegeBlockW = 50;
			$tonnegeBlockH = 40;
			$tonnegeArrowSize = 50;
			$tonnageSvg = new SVGDocumentFragment;
			$tonnageSvg->setAttribute('x', $footnoteConvas)->setAttribute('y', $footnoteConvas);
			foreach ($lowerSide->getElementsByClassName("Wheel") as $i=>$wheel) {
				$fulcrumX = $wheel->getAttribute('cx') * $scale;
				$fulcrumY = ($wheel->getAttribute('cy') + $wheel->getAttribute('r')) * $scale;
				$tonnageSvg
					->addChild(
						(new SVGGroup)
							->setAttribute('class', 'WheelTonnageGroup')
							->setAttribute('vehicleType', 0)
							->setAttribute('wheelNum', $i)
							->setAttribute('maxTonnage', $wheel->getAttribute('max_tonnage'))
							->setAttribute('vehicleType', $wheel->getAttribute('vehicleType'))
							->setAttribute('axle', $wheel->getAttribute('axle'))
							->setAttribute('num', $wheel->getAttribute('num'))
							->addChild(
								(new SVGRect($fulcrumX - $tonnegeBlockW/2, $fulcrumY, $tonnegeBlockW, $tonnegeBlockH))
									->setStyle('fill', '#fff')
									->setStyle('stroke', '#000')
									->setStyle('stroke-width', 2)
									->setAttribute('class', 'WheelTonnageBox')
							)
							->addChild(
								(new SVGText(0, $fulcrumX, $fulcrumY + $tonnegeBlockH/4))
									->setSize(16)
									->setAttribute("text-anchor", "middle")
									->setAttribute("dominant-baseline", "central")
									->setAttribute('class', 'WheelTonnageCurrent')
							)
							->addChild(
								(new SVGText($wheel->getAttribute('max_tonnage'), $fulcrumX, $fulcrumY + $tonnegeBlockH/4*3))
									->setSize(16)
									->setAttribute("text-anchor", "middle")
									->setAttribute("dominant-baseline", "central")
									->setAttribute('class', 'WheelTonnageMax')
							)
							->addChild(
								(new SVGPolygon([
									[($px = $fulcrumX-$tonnegeArrowSize/4), ($py = $fulcrumY+$tonnegeBlockH)],
									[$px, ($py = $py + $tonnegeArrowSize/2)],
									[($px = $px - $tonnegeArrowSize/4), $py],
									[($px = $px + $tonnegeArrowSize/2), ($py = $py+$tonnegeArrowSize/2)],
									[($px = $px + $tonnegeArrowSize/2), ($py = $py-$tonnegeArrowSize/2)],
									[($px = $px - $tonnegeArrowSize/4), $py],
									[$px, ($py = $py - $tonnegeArrowSize/2)],
								]))
									->setAttribute('class', 'WheelTonnageArrow')
							)
					);
				
			}
			$lowerSide->addChild($tonnageSvg);
			
			
			// back
			$rightLowerSide->addChild(
				(new SVGDocumentFragment)
					->setAttribute('id', 'semiTrailerContainer')
					->addChild($this->drawSemiTrailerBack(
						(new SVGGroup)->setAttribute('transform', "scale({$scale},{$scale})"),
						$backWidth, $backHeight, $scale
					))
					->setAttribute('x', $footnoteConvas)
					->setAttribute('y', $footnoteConvas)
			);
			
			
			
			$this->svgdocument->addChild($lowerSide);
			$this->svgdocument->addChild($topSide);
			$this->svgdocument->addChild($rightLowerSide);
			
			
			
			return $this->svg;
		} elseif ($this->trailer) {
			return $this->svg;
		} else {
			return new Exception("unknown kind");
		}
		
		
		
	}
	
	private function drawTruckProfile($container, $convasWidth, $convasHeight, $scale)
	{
		$clearance = 400;
		$wheel = 450;
		$baseHeight = $convasHeight-$clearance;
		$baseWidth = $baseHeight * (100/65.65);
		$backOffset = $baseWidth/3;
		$backHeight = 500;
		$backWidth = $this->row_truck['full_length']-$backOffset-$backBumperWidth;
		$backBumperWidth = $mudguardWidth = 100;
		$mudguardHeight = $backHeight;
		$truckType = 1;
		
		// truck cabin
		if ($this->row_truck['anchor_front_distance'] - $this->semi_trailer['anchor_front_distance'] > $baseWidth - $baseWidth/5) {
			$baseY = $convasHeight-$baseHeight-$clearance;
			$pathW = 800.2;
			$pathH = 525.31;
			$scaleX = $baseWidth / $pathW;
			$scaleY = $baseHeight / $pathH;
			$container->addChild(
				(new SVGDocumentFragment($convasWidth, $convasHeight))->addChild(
					(new SVGPath('M0,327.77V518.84a1.46,1.46,0,0,0,1.46,1.46H6.54A1.46,1.46,0,0,0,8,518.84V455.77a1.46,1.46,0,0,1,1.46-1.46H19.54A1.46,1.46,0,0,1,21,455.77v63.07a1.46,1.46,0,0,0,1.46,1.46h6.07A1.46,1.46,0,0,0,30,518.84V455.77a1.46,1.46,0,0,1,1.46-1.46H35.9c5.5,0,6.1-.2,8.5-3.3a1.46,1.46,0,0,1,2.6.92v70.91s1.06,1.46,1.87,1.46H50.3a4.57,4.57,0,0,1,2,.33c.22.1.45.17.69-.33H268.9s1.4.64,2,.87a1.53,1.53,0,0,0,.58.13H567.4c49.07,0,96.31,0,139.08-.07.66-6.79,2-13.48,2-20.37,0-8.16.09-16.22.55-24.34-.17-4.51,0-9,.24-13.55-.23-5.32-.81-10.6-1.42-16-.91-8.07-.78-16.16-.72-24.26-.19-4.46-.3-8.92-.31-13.39a5.16,5.16,0,0,1,.32-1.84l-.22-2.66c-.4-4.7-.5-8.8-.2-9.1s16.7-2.6,36.6-5.2c25.1-3.3,36.1-5.1,36.1-5.9s2.9-1.3,9.7-1.5l8.38-.26a1.62,1.62,0,0,0,1.51-1.46l1-114.78.2-114.48a1.46,1.46,0,0,0-1.42-1.47L790,154.4c-8.93-.3-10.34-.5-10.64-2.11s.7-1.71,9.74-1.71c9.84,0,10.14-.1,10.14-2.31-.1-1.2-3.11-26.4-6.83-55.91L785.8,39.9a1.46,1.46,0,0,0-1.41-1.28l-8.49-.26-8.36-.26a1.46,1.46,0,0,1-1.41-1.31l-.57-5.45C764,16.47,758.74,6,750.91,2.22c-8-4-13.65-3.31-65.15,8.23-172,38.45-168,37.54-183.9,43.27-8.43,3-15.46,5.52-15.56,5.52s-.3-11.95-.3-26.6V7.77a1.46,1.46,0,0,0-1.46-1.46H472.46A1.46,1.46,0,0,0,471,7.77V65.09a1.46,1.46,0,0,1-.82,1.32l-9.88,4.8a313.1,313.1,0,0,0-57.4,36.2c-10.2,8.2-30.7,27.9-38.6,37.2l-3.69,4.23a1.46,1.46,0,0,1-1,.5l-12.77.57-13,.67a1.46,1.46,0,0,0-.88.36L316.7,165l-16.2,14.06a1.46,1.46,0,0,0-.5,1.11v8.67a1.46,1.46,0,0,0,1.46,1.46H316c8.8,0,16,.2,16,.5s-10.6,21.4-23.6,47l-23.19,45.7a1.46,1.46,0,0,1-1.31.8h-2.7c-9.7,0-75.7,8.8-113.2,15-49.2,8.2-98.7,19.5-111.8,25.5-6,2.8-8.2,6.4-8.2,13.8,0,2.4-.3,7.2-.6,10.6l-.55,4.8a1.46,1.46,0,0,1-1.45,1.3H9.46A1.46,1.46,0,0,1,8,353.84V327.77a1.46,1.46,0,0,0-1.46-1.46H1.46A1.46,1.46,0,0,0,0,327.77Zm13.46,35.54H48.54A1.46,1.46,0,0,1,50,364.77v3.74c0,2.9-.5,10.7-1,17.3-1,13.6-.8,13.3-10.6,16.5-3.5,1.2-6.4,2.5-6.4,2.9s-1.4,10.1-3,21.4-3,21.2-3,21.7-3,1-7,1H13.46A1.46,1.46,0,0,1,12,447.84V364.77A1.46,1.46,0,0,1,13.46,363.31Z'))
						->setAttribute('transform', "scale({$scaleX},{$scaleY})")
				)
					->setAttribute('x', 0)
					->setAttribute('y', $baseY)
			);
			
			$truckBackY = $convasHeight-$clearance-$backHeight;
			$container->addChild(
				(new SVGRect($backOffset, $truckBackY, $backWidth, $backHeight))
					->setAttribute('id', 'RowTruckBack')
			);
			
			// backBumper
			$backBumperX = $backOffset + $backWidth-1;
			$backBumperY = $truckBackY+$backHeight/4;
			
			$container->addChild(
				(new SVGRect($backBumperX, $backBumperY, $backBumperWidth, $backHeight))
					->setAttribute('id', 'RowTruckBackBumper')
			);
		
		} else {
			$truckType = 2;
			$baseHeight = $convasHeight-$clearance/1.3;
			$baseWidth = $baseHeight * (100/126.45);
			$backHeight = 350;
			$clearance = 550;
			$mudguardWidth = 300;
			$mudguardHeight = 400;
			
			$baseY = $convasHeight-$baseHeight-$clearance/1.7;
			$pathW = 542.5;
			$pathH = 686;
			$scaleX = $baseWidth / $pathW;
			$scaleY = $baseHeight / $pathH;
			$container->addChild(
				(new SVGDocumentFragment($convasWidth, $convasHeight))->addChild(
					(new SVGPath('M532.29,67.2c-8.9.3-11.2.8-16.8,3.4C504.19,76,495.09,85,486,100.1l-5,8.3-.5,14.5-.5,14.6-4.2.3-4.3.3-.2,36.7-.3,36.7-7.2.3-7.3.3V168H454c-2.4,0-2.5-.3-2.5-5.5,0-4.2.3-5.5,1.5-5.5s1.5-3.8,1.5-28.5V100h-3.9c-4.6,0-4.6,0-5.5-6.1l-.7-4.6,6.1-1.7,6-1.7V25.3l-13.4-12.6L429.69,0l-4.9.1c-2.6.1-6.1.4-7.8.6-52.3,6.5-154,59.6-270.3,141.1l-19.2,13.5V168h-13.6L107,182.1c-3.9,7.8-7.4,14.7-8,15.3a122.25,122.25,0,0,0-6.2,11.6l-5.3,10.5.3-8.3c.2-6.9.5-8.3,2-8.8,1-.3,1.7-.7,1.5-.9s-2.2.2-4.3,1a32.87,32.87,0,0,1-8.3,1.5c-4,0-4.4.3-7.2,4.7-8,12.6-17,47.7-17,66.3v9.9l-18.6,36.3-18.7,36.3.2,7.5c.1,4.1,1.1,28.2,2.1,53.5s2,46.3,2,46.7-4.2.8-9.4.8H2.69l-1.3,8.2c-2.2,13.3-1.7,39.3.9,50.1A82.91,82.91,0,0,0,6,536c1.5,3,1.6,3,10,3h8.5v3.2c0,1.8.3,5.7.6,8.5l.7,5.3h11.7v22h-5.3l.6,16.7c.4,9.3,1.1,25.4,1.7,35.9l1,19.1,6.3,17.9,6.4,17.9,58.6.3,58.6.2.6-5.7c1.2-11,3.6-26.8,5.4-35.3l1.7-8.5,9.2-.3,9.1-.3v.32h195.1V636h6.8l0,.22h2.49a118.67,118.67,0,0,1,20.41.23c14.89-.25,29.78-.81,44.6.3V553.89a5,5,0,0,1,.57-2.39h-.91l2.26-1.52h0l5.94-4A137.57,137.57,0,0,0,485,532.9c12.1-11.6,18.3-23.4,18.9-36.2l.3-5.2,5.1-.3,5.2-.3V407.1l5.8-.3c5.3-.3,5.7-.5,5.7-2.8s-.4-2.5-5.7-2.8l-5.8-.3V218h4.1c4.9,0,7.9-1.5,7.9-3.9s-1.9-3.1-7.6-3.1h-4.4V138h-4.4c-2.5,0-4.7-.3-4.9-.8s-.1-6.4.3-13.3c.9-14.9,2.9-22.2,7.8-28,5.2-6,9.9-7.9,20.5-7.9h8.7V66.8Zm-78.4,300.5c.3-18.2.8-51.7,1.1-74.2s.8-48.8,1.1-58.3l.6-17.2h14.8V401h-18.3Zm31.2,128.4c-1.1,8.3-5.3,20-9.1,25.7-3.4,5-11.3,11.2-14.2,11.2-1,0-1.3-3.5-1.3-16V501h-18V488.1l4.8-.3,4.7-.3.6-33.5c.3-18.4.7-36.3.8-39.7l.1-6.2,3.8-.4a77.78,77.78,0,0,1,8.7-.3l5,.1.3,41.7.2,41.8h14.3Z'))
						->setAttribute('transform', "scale({$scaleX},{$scaleY})")
				)
					->setAttribute('x', 0)
					->setAttribute('y', $baseY)
			);
			
			$truckBackY = $convasHeight-$clearance-$backHeight;
			$container->addChild(
				(new SVGRect($backOffset, $truckBackY, $backWidth+$backBumperWidth, $backHeight))
					->setAttribute('id', 'RowTruckBack')
			);
			
		}
		
		// front wheels
		$axleFrontWidth = $this->row_truck['axle_front']['num_of_wheels']*$this->row_truck['axle_front']['wheel_distance'];
		$axleFrontX = $this->row_truck['axle_front']['front_distance'] - $axleFrontWidth/2;
		$axleFrontY = $convasHeight - $wheel;
		for ($i = 0; $i < $this->row_truck['axle_front']['num_of_wheels']; $i++) {
			$wheelCenterX = $axleFrontX + $this->row_truck['axle_front']['wheel_distance'] * ($i+0.5);
			$container->addChild(
				(new SVGCircle($wheelCenterX, $axleFrontY, $wheel))
					->setAttribute('id', 'RowTruckFrontWheel'.$i)
					->setAttribute('class', 'RowTruckFrontWheel Wheel')
					->setAttribute('max_tonnage', $this->row_truck['axle_front']['max_tonnage'])
					->setAttribute('vehicleType', 'rowTruck')
					->setAttribute('axle', 'front')
					->setAttribute('num', $i)
			);
			$container->addChild(
				(new SVGCircle($wheelCenterX, $axleFrontY, $wheel-200))
					->setStyle('fill', '#fff')
					->setAttribute('id', 'RowTruckFrontRim'.$i)
					->setAttribute('class', 'RowTruckFrontRim')
			);
		}
		
		// mudguard
		$mudguardX = $axleFrontX + $axleFrontWidth + 80;
		$container->addChild(
			(new SVGRect($mudguardX, $truckBackY+$backHeight/4, $mudguardWidth, $mudguardHeight))
				->setAttribute('id', 'RowTruckMudguard')
		);
		
		// back wheels
		$axle_back_x = $this->row_truck['full_length'] - $this->row_truck['axle_back']['back_distance'] -
			$this->row_truck['axle_back']['num_of_wheels']*$this->row_truck['axle_back']['wheel_distance']/2;
		$axle_back_y = $convasHeight - $wheel;
		for ($i = 0; $i < $this->row_truck['axle_back']['num_of_wheels']; $i++) {
			$wheel_center_x = $axle_back_x + $this->row_truck['axle_back']['wheel_distance'] * ($i+0.5);
			$container->addChild(
				(new SVGCircle($wheel_center_x, $axle_back_y, $wheel))
					->setAttribute('id', 'RowTruckBackWheel'.$i)
					->setAttribute('class', 'RowTruckBackWheel Wheel')
					->setAttribute('max_tonnage', $this->row_truck['axle_back']['max_tonnage'])
					->setAttribute('vehicleType', 'rowTruck')
					->setAttribute('axle', 'back')
					->setAttribute('num', $i)
			);
			$container->addChild(
				(new SVGCircle($wheel_center_x, $axle_back_y, $wheel-200))
					->setStyle('fill', '#fff')
					->setAttribute('id', 'RowTruckBackRim'.$i)
					->setAttribute('class', 'RowTruckBackRim')
			);
		}
		
		// gas tank
		if ($truckType == 2) {
			$tankX = $mudguardX + $mudguardWidth + 100;
			$tankWidth = $axle_back_x - $tankX - 100;
			$container->addChild(
				(new SVGRect($tankX, $truckBackY+$backHeight/4, $tankWidth, $mudguardHeight))
					->setAttribute('id', 'RowTruckGastank')
			);
			
		}
		
		// anchor
		$anchorHeight = 100;
		$anchorWidth = $anchorHeight * (100/24.91);
		
		
		$pathW = 124;
		$pathH = 30.89;
		$scaleX = $anchorWidth / $pathW;
		$scaleY = $anchorHeight / $pathH;
		$container->addChild(
			(new SVGDocumentFragment($convasWidth, $convasHeight))->addChild(
				(new SVGPath('M77,30.7h.81l0,.19H12.43s0-.06,0-.09H13V20H0V13C0,8.3.4,6,1.2,6A21.5,21.5,0,0,0,6.4,4c5.7-2.7,15.1-4,29.1-4S58.9,1.3,64.6,4c3.7,1.8,6.3,2,31.7,2H124V20H77Z'))
					->setAttribute('transform', "scale({$scaleX},{$scaleY})")
			)
				->setAttribute('x', $this->row_truck['anchor_front_distance'] - $anchorWidth/3)
				->setAttribute('y', $truckBackY-$anchorHeight)
		);
		return $container;
	}
	
	private function drawSemiTrailerProfile($container, $convasWidth, $convasHeight, $scale)
	{
		$clearance = 500;
		$wheel = 450;
		$mudguardWidth = 100;
		$trailerBaseHeight = 300;
		
		$trailerX = $this->row_truck['anchor_front_distance'] - $this->semi_trailer['anchor_front_distance'];
		$trailerY = $convasHeight - $clearance*2 - $this->semi_trailer['height'];
		
		// trailer base
		$trailerBaseX = $this->semi_trailer['length']/3 + $trailerX;
		$trailerBaseY = $trailerY + $this->semi_trailer['height'];
		$trailerBaseW = $this->semi_trailer['length']/3*2;
		
		$container->addChild(
			(new SVGRect($trailerBaseX, $trailerBaseY, $trailerBaseW, $trailerBaseHeight))
				->setAttribute('id', 'SemiTrailerBase')
		);
		
		$axleBaseX = $trailerX + $this->semi_trailer['length'] - $this->semi_trailer['axle_back']['back_distance'] -
			$this->semi_trailer['axle_back']['num_of_wheels'] * $this->semi_trailer['axle_back']['wheel_distance']/2;
		$axleBaseY = $trailerY + $this->semi_trailer['height'];
		$axleBaseW = $this->semi_trailer['axle_back']['wheel_distance'] * $this->semi_trailer['axle_back']['num_of_wheels'];
		$container->addChild(
			(new SVGRect($axleBaseX, $axleBaseY, $axleBaseW, $wheel))
				->setAttribute('id', 'SemiTrailerBackAxleBase')
		);
		
		
		// mudguard
		$mudguardX = $axleBaseX + $axleBaseW;
		$container->addChild(
			(new SVGRect($mudguardX, $axleBaseY+$wheel/2, $mudguardWidth, $wheel))
				->setAttribute('id', 'SemiTrailerMudguard')
		);
		
		// spare wheel
		if (($axleBaseX + $axleBaseW + 1000 + $wheel*2) < $trailerX + $this->semi_trailer['length']) {
			$spareX = $mudguardX + 700;
			$spareY = $trailerBaseY + $trailerBaseHeight + 30;
			$container->addChild(
				(new SVGRect($spareX, $spareY, $wheel*2, 350))
					->setAttribute('id', 'SemiTrailerSpareWheel')
			);
			
			$container->addChild(
				(new SVGRect($spareX-100, $spareY-30, 100, 400))
					->setAttribute('id', 'SemiTrailerSpareWheelLeftRack')
			);
			$container->addChild(
				(new SVGRect($spareX+$wheel*2, $spareY-30, 100, 400))
					->setAttribute('id', 'SemiTrailerSpareWheelRightRack')
			);
		}
		
		// bumper
		$bumpX = $trailerBaseX + 500;
		$bumpY = $trailerBaseY + $trailerBaseHeight + 100;
		$bumpW = $axleBaseX - 500 - $bumpX;
		$container->addChild(
			(new SVGRect($bumpX, $bumpY, $bumpW, 200))
				->setAttribute('id', 'SemiTrailerBumper')
		);
		
		$container->addChild(
			(new SVGRect($bumpX+100, $trailerBaseY + $trailerBaseHeight, 100, 200))
				->setAttribute('id', 'SemiTrailerBumperLeftRack')
		);
		
		$container->addChild(
			(new SVGRect($bumpX+$bumpW-200, $trailerBaseY + $trailerBaseHeight, 100, 200))
				->setAttribute('id', 'SemiTrailerBumperRightRack')
		);
		
		
		// trailer axle
		$axle_back_x = $trailerX + $this->semi_trailer['length'] - $this->semi_trailer['axle_back']['back_distance'] -
			$this->semi_trailer['axle_back']['num_of_wheels'] * $this->semi_trailer['axle_back']['wheel_distance']/2;
		$axle_back_y = $convasHeight - $wheel;
		for ($i = 0; $i < $this->semi_trailer['axle_back']['num_of_wheels']; $i++) {
			$wheel_center_x = $axle_back_x + $this->semi_trailer['axle_back']['wheel_distance'] * ($i+0.5);
			$container->addChild(
				(new SVGCircle($wheel_center_x, $axle_back_y, $wheel))
					->setAttribute('id', 'SemiTrailerBackWheel'.$i)
					->setAttribute('class', 'SemiTrailerBackWheel Wheel')
					->setAttribute('max_tonnage', $this->semi_trailer['axle_back']['max_tonnage'])
					->setAttribute('vehicleType', 'semiTrailer')
					->setAttribute('axle', 'back')
					->setAttribute('num', $i)
			);
			$container->addChild(
				(new SVGCircle($wheel_center_x, $axle_back_y, $wheel-200))
					->setStyle('fill', '#fff')
					->setAttribute('id', 'SemiTrailerBackRim'.$i)
					->setAttribute('class', 'SemiTrailerBackWheelRim')
			);
		}
		
		// trailer main
		$container->addChild(
			(new SVGRect($trailerX, $trailerY, $this->semi_trailer['length'], $this->semi_trailer['height']))
				->setStyle('fill', '#fff')
				->setStyle('stroke', '#000')
				->setStyle('stroke-width', 5/$scale . 'px')
				->setAttribute('id', 'SemiTrailerProfile')
				->setAttribute('data-length', $this->semi_trailer['length'])
				->setAttribute('data-width', $this->semi_trailer['width'])
				->setAttribute('data-height', $this->semi_trailer['height'])
		);
		return $container;
	
	}
	
	private function drawSemiTrailerTop($container, $convasWidth, $convasHeight, $scale)
	{
		
		$trailerX = $this->row_truck['anchor_front_distance'] - $this->semi_trailer['anchor_front_distance'];
		$trailerY = 0;
		
		// trailer main
		$container->addChild(
			(new SVGRect($trailerX, $trailerY, $this->semi_trailer['length'], $this->semi_trailer['width']))
				->setStyle('fill', '#fff')
				->setStyle('stroke', '#000')
				->setStyle('stroke-width', 5/$scale . 'px')
				->setAttribute('id', 'SemiTrailerTop')
				->setAttribute('data-length', $this->semi_trailer['length'])
				->setAttribute('data-width', $this->semi_trailer['width'])
				->setAttribute('data-height', $this->semi_trailer['height'])
		);
		return $container;
	}
	
	private function drawSemiTrailerBack($container, $convasWidth, $convasHeight, $scale)
	{
		$clearance = 500;
		$wheel = 450;
		$mudguardWidth = 100;
		$trailerBaseHeight = 300;
		$trailerX = 0;
		$trailerY = $convasHeight - $clearance*2 - $this->semi_trailer['height'];
		
		// trailer base + axle base
		$trailerBaseX = $axleBaseX = $trailerX;
		$trailerBaseY = $trailerY + $this->semi_trailer['height'];
		$trailerBaseW = $this->semi_trailer['width'];
		
		$container->addChild(
			(new SVGRect($trailerBaseX, $trailerBaseY, $trailerBaseW, $wheel))
				->setAttribute('id', 'SemiTrailerBase')
		);
		
		
		// spare wheel
		if (($axleBaseX + $axleBaseW + 1000 + $wheel*2) < $trailerX + $this->semi_trailer['length']) {
			$spareX = $this->semi_trailer['width'] / 2 - $wheel;
			$spareY = $trailerBaseY + $trailerBaseHeight + 30;
			$container->addChild(
				(new SVGRect($spareX, $spareY, $wheel*2, 350))
					->setAttribute('id', 'SemiTrailerSpareWheel')
			);
			
			$container->addChild(
				(new SVGRect($spareX-100, $spareY-30, 100, 400))
					->setAttribute('id', 'SemiTrailerSpareWheelLeftRack')
			);
			$container->addChild(
				(new SVGRect($spareX+$wheel*2, $spareY-30, 100, 400))
					->setAttribute('id', 'SemiTrailerSpareWheelRightRack')
			);
		}
		
		// wheels
		// left
		$container->addChild(
			(new SVGRect($trailerX, $trailerBaseY, 350, $wheel*2))
				->setAttribute('class', 'wheel')
		);
		$container->addChild(
			(new SVGRect($trailerX + 350 + 30, $trailerBaseY, 350, $wheel*2))
				->setAttribute('class', 'wheel')
		);
		// right
		$container->addChild(
			(new SVGRect($trailerX+$this->semi_trailer['width']-350*2-30, $trailerBaseY, 350, $wheel*2))
				->setAttribute('class', 'wheel')
		);
		$container->addChild(
			(new SVGRect($trailerX+$this->semi_trailer['width']-350, $trailerBaseY, 350, $wheel*2))
				->setAttribute('class', 'wheel')
		);
		
		// mudguard
		$container->addChild(
			(new SVGRect($trailerX, $trailerBaseY+$wheel/2, 350*2+30, $wheel))
				->setAttribute('class', 'mudguard')
		);
		$container->addChild(
			(new SVGRect($trailerX+$this->semi_trailer['width']-350*2-30, $trailerBaseY+$wheel/2, 350*2+30, $wheel))
				->setAttribute('class', 'mudguard')
		);
		
		// trailer main
		$container->addChild(
			(new SVGRect($trailerX, $trailerY, $this->semi_trailer['width'], $this->semi_trailer['height']))
				->setStyle('fill', '#fff')
				->setStyle('stroke', '#000')
				->setStyle('stroke-width', 5/$scale . 'px')
				->setAttribute('id', 'SemiTrailerBack')
				->setAttribute('data-length', $this->semi_trailer['length'])
				->setAttribute('data-width', $this->semi_trailer['width'])
				->setAttribute('data-height', $this->semi_trailer['height'])
		);
		return $container;
	}
	
	private function drawSizeFootnote($title, $size, $x, $y, $length, $orientation = self::SIZE_FOOTNOTE_HORIZONTAL, $width = 2, $arrowSize = 4, $fontSize = 12)
	{
		$g = (new SVGGroup)
			->setAttribute('class', 'size-footnote')
			->addChild(
				(new SVGTitle())->setValue($title)
			);
		if ($orientation == self::SIZE_FOOTNOTE_VERTICAL) { // vertical
			$g	->addChild(
					(new SVGRect($x, $y, $width, $length))
				)
				->addChild(
					(new SVGPolygon([[($cx = $x+$width/2), $y], [$cx + $arrowSize, $y + $arrowSize], [$cx - $arrowSize, $y + $arrowSize]]))
				)
				->addChild(
					(new SVGPolygon([[$cx, ($ey = $y + $length)], [$cx + $arrowSize, $ey - $arrowSize], [$cx - $arrowSize, $ey - $arrowSize]]))
				)
				->addChild(
					(new SVGText($size, ($x = $x - $fontSize/2), ($y = $y + $length/2)))
						->setSize($fontSize)
						->setAttribute("text-anchor", "middle")
						->setAttribute("dominant-baseline", "central")
						->setAttribute("transform", "rotate(-90, {$x}, {$y})")
				);
		} elseif ($orientation == self::SIZE_FOOTNOTE_HORIZONTAL) { // horisontal
			$g	->addChild(
					(new SVGRect($x, $y, $length, $width))
				)
				->addChild(
					(new SVGPolygon([[$x, ($cy = $y + $width/2)], [$x+$arrowSize, $cy+$arrowSize], [$x+$arrowSize, $cy-$arrowSize]]))
				)
				->addChild(
					(new SVGPolygon([[($ex = $x+$length), $cy], [$ex-$arrowSize, $cy+$arrowSize], [$ex-$arrowSize, $cy-$arrowSize]]))
				)
				->addChild(
					(new SVGText($size, ($x = $x + $length/2), ($y = $y-$fontSize/2)))
						->setSize($fontSize)
						->setAttribute("text-anchor", "middle")
						->setAttribute("dominant-baseline", "central")
				);
		}
		return $g;
	}
	
}