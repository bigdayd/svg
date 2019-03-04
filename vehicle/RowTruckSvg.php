<?php
namespace ils\svg\vehicle;

use SVG\SVG;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Shapes\SVGCircle;
use SVG\Nodes\Shapes\SVGPolygon;
use SVG\Nodes\Shapes\SVGPath;
// use SVG\Nodes\Texts\SVGText;
// use SVG\Nodes\Texts\SVGTitle;
use SVG\Nodes\Structures\SVGGroup;
use SVG\Nodes\Structures\SVGDocumentFragment;

	
class RowTruckSvg extends VehicleSvg{
	
	const CLEARANCE = 400;
	const WHEEL = 450;
	
	public $_truckType = 0;
	private $_defaultHeight = 2900;
	private $trailer;
	
	public function getSizes()
	{
		// ['title', 'size', 'orientation'(, 'startOffset', 'real_length')]
		return [
			'FullLength'=>['FullLength', $this->FullLength, VehicleFootnotesSvg::SIZE_FOOTNOTE_HORIZONTAL],
			'FullHeight'=>['FullHeight', $this->FullHeight, VehicleFootnotesSvg::SIZE_FOOTNOTE_VERTICAL, 0, $this->getProfileHeight()],
			'AnchorFrontDistance'=>['AnchorFrontDistance', $this->AnchorFrontDistance, VehicleFootnotesSvg::SIZE_FOOTNOTE_HORIZONTAL],
			'AxleFrontDistance'=>['AxleFrontDistance', $this->AxleFrontDistance, VehicleFootnotesSvg::SIZE_FOOTNOTE_HORIZONTAL],
			'AxleBackDistance'=>['AxleBackDistance', $this->AxleBackDistance, VehicleFootnotesSvg::SIZE_FOOTNOTE_HORIZONTAL, $this->FullLength-$this->AxleBackDistance],
			
		];
	}

	
	public function getProfile()
	{
		$container = (new SVGGroup);
		$this->DrawCabinProfile($container);
		
		// front wheels
		$axleFrontWidth = $this->AxleFrontWheelsCount * ($this->AxleFrontWheelDistance ? $this->AxleFrontWheelDistance : 1000);
		$axleFrontX = $this->AxleFrontDistance - $axleFrontWidth/2;
		$axleFrontY = $this->getProfileHeight() - self::WHEEL;
		for ($i = 0; $i < $this->AxleFrontWheelsCount; $i++) {
			$wheelCenterX = $axleFrontX + ($this->AxleFrontWheelDistance ? $this->AxleFrontWheelDistance : 1000) * ($i+0.5);
			$container->addChild(
				(new SVGCircle($wheelCenterX, $axleFrontY, self::WHEEL))
					->setAttribute('id', 'RowTruckFrontWheel'.$i)
					->setAttribute('class', 'RowTruckFrontWheel Wheel')
					->setAttribute('max_tonnage', $this->AxleFrontWheelMaxTonnage)
					->setAttribute('vehicleType', 'rowTruck')
					->setAttribute('axle', 'front')
					->setAttribute('num', $i)
			);
			$container->addChild(
				(new SVGCircle($wheelCenterX, $axleFrontY, self::WHEEL-200))
					->setStyle('fill', '#fff')
					->setAttribute('id', 'RowTruckFrontRim'.$i)
					->setAttribute('class', 'RowTruckFrontRim')
			);
		}
		
		// back wheels
		$axleBackX = $this->FullLength - $this->AxleBackDistance -
						$this->AxleBackWheelsCount * ($this->AxleBackWheelDistance ? $this->AxleBackWheelDistance : 1000)/2;
		$axleBackY = $this->getProfileHeight() - self::WHEEL;
		for ($i = 0; $i < $this->AxleBackWheelsCount; $i++) {
			$wheelCenterX = $axleBackX + ($this->AxleBackWheelDistance ? $this->AxleBackWheelDistance : 1000) * ($i+0.5);
			$container->addChild(
				(new SVGCircle($wheelCenterX, $axleBackY, self::WHEEL))
					->setAttribute('id', 'RowTruckBackWheel'.$i)
					->setAttribute('class', 'RowTruckBackWheel Wheel')
					->setAttribute('max_tonnage', $this->AxleBackWheelMaxTonnage)
					->setAttribute('vehicleType', 'rowTruck')
					->setAttribute('axle', 'back')
					->setAttribute('num', $i)
			);
			$container->addChild(
				(new SVGCircle($wheelCenterX, $axleBackY, self::WHEEL-200))
					->setStyle('fill', '#fff')
					->setAttribute('id', 'RowTruckBackRim'.$i)
					->setAttribute('class', 'RowTruckBackRim')
			);
		}
		
		return (new SVGDocumentFragment)
			->addChild($container)
			->setAttribute('width', $this->getProfileWidth())
			->setAttribute('height', $this->getProfileHeight());
	}
	
	private function DrawCabinProfile($container)
    {
        if (isset($this->trailer)) {
            if ($this->AnchorFrontDistance - $this->trailer->AnchorFrontDistance < (($this->getProfileHeight() - self::CLEARANCE) * (100 / 65.65)) / 5 * 4) {
                $this->_truckType = 1;
            }
        }
		// truck cabin
		switch ($this->_truckType) {
			case 0:
				$baseHeight = $this->getProfileHeight() - self::CLEARANCE;
				$baseWidth = $baseHeight * (100/65.65);
				$backOffset = $baseWidth / 3;
				$backHeight = $mudguardHeight = 500;
				$backBumperWidth = $mudguardWidth = 100;
				$backWidth = $this->FullLength - $backOffset - $backBumperWidth;
				$pathW = 800.2;
				$pathH = 525.31;
				$scaleX = $baseWidth / $pathW;
				$scaleY = $baseHeight / $pathH;
				$container->addChild(
					(new SVGDocumentFragment($baseWidth, $baseHeight))->addChild(
						(new SVGPath('M0,327.77V518.84a1.46,1.46,0,0,0,1.46,1.46H6.54A1.46,1.46,0,0,0,8,518.84V455.77a1.46,1.46,0,0,1,1.46-1.46H19.54A1.46,1.46,0,0,1,21,455.77v63.07a1.46,1.46,0,0,0,1.46,1.46h6.07A1.46,1.46,0,0,0,30,518.84V455.77a1.46,1.46,0,0,1,1.46-1.46H35.9c5.5,0,6.1-.2,8.5-3.3a1.46,1.46,0,0,1,2.6.92v70.91s1.06,1.46,1.87,1.46H50.3a4.57,4.57,0,0,1,2,.33c.22.1.45.17.69-.33H268.9s1.4.64,2,.87a1.53,1.53,0,0,0,.58.13H567.4c49.07,0,96.31,0,139.08-.07.66-6.79,2-13.48,2-20.37,0-8.16.09-16.22.55-24.34-.17-4.51,0-9,.24-13.55-.23-5.32-.81-10.6-1.42-16-.91-8.07-.78-16.16-.72-24.26-.19-4.46-.3-8.92-.31-13.39a5.16,5.16,0,0,1,.32-1.84l-.22-2.66c-.4-4.7-.5-8.8-.2-9.1s16.7-2.6,36.6-5.2c25.1-3.3,36.1-5.1,36.1-5.9s2.9-1.3,9.7-1.5l8.38-.26a1.62,1.62,0,0,0,1.51-1.46l1-114.78.2-114.48a1.46,1.46,0,0,0-1.42-1.47L790,154.4c-8.93-.3-10.34-.5-10.64-2.11s.7-1.71,9.74-1.71c9.84,0,10.14-.1,10.14-2.31-.1-1.2-3.11-26.4-6.83-55.91L785.8,39.9a1.46,1.46,0,0,0-1.41-1.28l-8.49-.26-8.36-.26a1.46,1.46,0,0,1-1.41-1.31l-.57-5.45C764,16.47,758.74,6,750.91,2.22c-8-4-13.65-3.31-65.15,8.23-172,38.45-168,37.54-183.9,43.27-8.43,3-15.46,5.52-15.56,5.52s-.3-11.95-.3-26.6V7.77a1.46,1.46,0,0,0-1.46-1.46H472.46A1.46,1.46,0,0,0,471,7.77V65.09a1.46,1.46,0,0,1-.82,1.32l-9.88,4.8a313.1,313.1,0,0,0-57.4,36.2c-10.2,8.2-30.7,27.9-38.6,37.2l-3.69,4.23a1.46,1.46,0,0,1-1,.5l-12.77.57-13,.67a1.46,1.46,0,0,0-.88.36L316.7,165l-16.2,14.06a1.46,1.46,0,0,0-.5,1.11v8.67a1.46,1.46,0,0,0,1.46,1.46H316c8.8,0,16,.2,16,.5s-10.6,21.4-23.6,47l-23.19,45.7a1.46,1.46,0,0,1-1.31.8h-2.7c-9.7,0-75.7,8.8-113.2,15-49.2,8.2-98.7,19.5-111.8,25.5-6,2.8-8.2,6.4-8.2,13.8,0,2.4-.3,7.2-.6,10.6l-.55,4.8a1.46,1.46,0,0,1-1.45,1.3H9.46A1.46,1.46,0,0,1,8,353.84V327.77a1.46,1.46,0,0,0-1.46-1.46H1.46A1.46,1.46,0,0,0,0,327.77Zm13.46,35.54H48.54A1.46,1.46,0,0,1,50,364.77v3.74c0,2.9-.5,10.7-1,17.3-1,13.6-.8,13.3-10.6,16.5-3.5,1.2-6.4,2.5-6.4,2.9s-1.4,10.1-3,21.4-3,21.2-3,21.7-3,1-7,1H13.46A1.46,1.46,0,0,1,12,447.84V364.77A1.46,1.46,0,0,1,13.46,363.31Z'))
							->setAttribute('transform', "scale({$scaleX},{$scaleY})")
					)
				);
				
				$truckBackY = $this->getProfileHeight()-self::CLEARANCE-$backHeight;
				$container->addChild(
					(new SVGRect($backOffset, $truckBackY, $backWidth, $backHeight))
						->setAttribute('id', 'RowTruckBack')
				);
				
				$axleFrontWidth = $this->AxleFrontWheelsCount * ($this->AxleFrontWheelDistance ? $this->AxleFrontWheelDistance : 1000);
				$axleFrontX = $this->AxleFrontDistance - $axleFrontWidth/2;
				$axleBackX = $this->FullLength - $this->AxleBackDistance -
				$this->AxleBackWheelsCount * ($this->AxleBackWheelDistance ? $this->AxleBackWheelDistance : 1000) / 2;
				
				// mudguard
				$mudguardX = $axleFrontX + $axleFrontWidth + 80;
				$container->addChild(
					(new SVGRect($mudguardX, $truckBackY+$backHeight/4, $mudguardWidth, $mudguardHeight))
						->setAttribute('id', 'RowTruckMudguard')
				);
				
				// backBumper
				$backBumperX = $backOffset + $backWidth-1;
				$backBumperY = $truckBackY+$backHeight/4;
				
				$container->addChild(
					(new SVGRect($backBumperX, $backBumperY, $backBumperWidth, $backHeight))
						->setAttribute('id', 'RowTruckBackBumper')
				);
				
				// anchor
				$anchorHeight = 100;
				$anchorWidth = $anchorHeight * (100/24.91);
				$pathW = 124;
				$pathH = 30.89;
				$scaleX = $anchorWidth / $pathW;
				$scaleY = $anchorHeight / $pathH;
				$container->addChild(
					(new SVGDocumentFragment($anchorWidth, $anchorHeight))->addChild(
						(new SVGPath('M77,30.7h.81l0,.19H12.43s0-.06,0-.09H13V20H0V13C0,8.3.4,6,1.2,6A21.5,21.5,0,0,0,6.4,4c5.7-2.7,15.1-4,29.1-4S58.9,1.3,64.6,4c3.7,1.8,6.3,2,31.7,2H124V20H77Z'))
							->setAttribute('transform', "scale({$scaleX},{$scaleY})")
					)
						->setAttribute('x', $this->AnchorFrontDistance - $anchorWidth/3)
						->setAttribute('y', $this->getProfileHeight() - self::CLEARANCE - $anchorHeight - $backHeight)
				);
			break;
			case 1:
				$baseHeight = $this->getProfileHeight() - self::CLEARANCE/1.3;
				$baseWidth = $baseHeight * (100/126.45);
				$backHeight = 350;
				$backOffset = $baseWidth / 3;
                $backBumperWidth = 100;
				$backWidth = $this->FullLength - $backOffset - $backBumperWidth;
				$mudguardWidth = 300;
				$mudguardHeight = 400;
				$pathW = 542.5;
				$pathH = 686;
				$scaleX = $baseWidth / $pathW;
				$scaleY = $baseHeight / $pathH;
				$container->addChild(
					(new SVGDocumentFragment($baseWidth, $baseHeight))->addChild(
						(new SVGPath('M532.29,67.2c-8.9.3-11.2.8-16.8,3.4C504.19,76,495.09,85,486,100.1l-5,8.3-.5,14.5-.5,14.6-4.2.3-4.3.3-.2,36.7-.3,36.7-7.2.3-7.3.3V168H454c-2.4,0-2.5-.3-2.5-5.5,0-4.2.3-5.5,1.5-5.5s1.5-3.8,1.5-28.5V100h-3.9c-4.6,0-4.6,0-5.5-6.1l-.7-4.6,6.1-1.7,6-1.7V25.3l-13.4-12.6L429.69,0l-4.9.1c-2.6.1-6.1.4-7.8.6-52.3,6.5-154,59.6-270.3,141.1l-19.2,13.5V168h-13.6L107,182.1c-3.9,7.8-7.4,14.7-8,15.3a122.25,122.25,0,0,0-6.2,11.6l-5.3,10.5.3-8.3c.2-6.9.5-8.3,2-8.8,1-.3,1.7-.7,1.5-.9s-2.2.2-4.3,1a32.87,32.87,0,0,1-8.3,1.5c-4,0-4.4.3-7.2,4.7-8,12.6-17,47.7-17,66.3v9.9l-18.6,36.3-18.7,36.3.2,7.5c.1,4.1,1.1,28.2,2.1,53.5s2,46.3,2,46.7-4.2.8-9.4.8H2.69l-1.3,8.2c-2.2,13.3-1.7,39.3.9,50.1A82.91,82.91,0,0,0,6,536c1.5,3,1.6,3,10,3h8.5v3.2c0,1.8.3,5.7.6,8.5l.7,5.3h11.7v22h-5.3l.6,16.7c.4,9.3,1.1,25.4,1.7,35.9l1,19.1,6.3,17.9,6.4,17.9,58.6.3,58.6.2.6-5.7c1.2-11,3.6-26.8,5.4-35.3l1.7-8.5,9.2-.3,9.1-.3v.32h195.1V636h6.8l0,.22h2.49a118.67,118.67,0,0,1,20.41.23c14.89-.25,29.78-.81,44.6.3V553.89a5,5,0,0,1,.57-2.39h-.91l2.26-1.52h0l5.94-4A137.57,137.57,0,0,0,485,532.9c12.1-11.6,18.3-23.4,18.9-36.2l.3-5.2,5.1-.3,5.2-.3V407.1l5.8-.3c5.3-.3,5.7-.5,5.7-2.8s-.4-2.5-5.7-2.8l-5.8-.3V218h4.1c4.9,0,7.9-1.5,7.9-3.9s-1.9-3.1-7.6-3.1h-4.4V138h-4.4c-2.5,0-4.7-.3-4.9-.8s-.1-6.4.3-13.3c.9-14.9,2.9-22.2,7.8-28,5.2-6,9.9-7.9,20.5-7.9h8.7V66.8Zm-78.4,300.5c.3-18.2.8-51.7,1.1-74.2s.8-48.8,1.1-58.3l.6-17.2h14.8V401h-18.3Zm31.2,128.4c-1.1,8.3-5.3,20-9.1,25.7-3.4,5-11.3,11.2-14.2,11.2-1,0-1.3-3.5-1.3-16V501h-18V488.1l4.8-.3,4.7-.3.6-33.5c.3-18.4.7-36.3.8-39.7l.1-6.2,3.8-.4a77.78,77.78,0,0,1,8.7-.3l5,.1.3,41.7.2,41.8h14.3Z'))
							->setAttribute('transform', "scale({$scaleX},{$scaleY})")
					)
				);
				
				$truckBackY = $baseHeight - self::CLEARANCE/1.7 - $backHeight;
				$container->addChild(
					(new SVGRect($backOffset, $truckBackY, $backWidth+$backBumperWidth, $backHeight))
						->setAttribute('id', 'RowTruckBack')
				);
				
				$axleFrontWidth = $this->AxleFrontWheelsCount * ($this->AxleFrontWheelDistance ? $this->AxleFrontWheelDistance : 1000);
				$axleFrontX = $this->AxleFrontDistance - $axleFrontWidth/2;
				$axleBackX = $this->FullLength - $this->AxleBackDistance -
								$this->AxleBackWheelsCount * ($this->AxleBackWheelDistance ? $this->AxleBackWheelDistance : 1000) / 2;
				
				// mudguard
				
		
				$mudguardX = $axleFrontX + $axleFrontWidth + 80;
				$container->addChild(
					(new SVGRect($mudguardX, $truckBackY+$backHeight/4, $mudguardWidth, $mudguardHeight))
						->setAttribute('id', 'RowTruckMudguard')
				);
				
				// gas tank
				$tankX = $mudguardX + $mudguardWidth + 100;
				$tankWidth = $axleBackX - $tankX - 100;
				$container->addChild(
					(new SVGRect($tankX, $truckBackY+$backHeight/4, $tankWidth, $mudguardHeight))
						->setAttribute('id', 'RowTruckGastank')
				);
				
				// anchor
				$anchorHeight = 100;
				$anchorWidth = $anchorHeight * (100/24.91);
				$pathW = 124;
				$pathH = 30.89;
				$scaleX = $anchorWidth / $pathW;
				$scaleY = $anchorHeight / $pathH;
				$container->addChild(
					(new SVGDocumentFragment($anchorWidth, $anchorHeight))->addChild(
						(new SVGPath('M77,30.7h.81l0,.19H12.43s0-.06,0-.09H13V20H0V13C0,8.3.4,6,1.2,6A21.5,21.5,0,0,0,6.4,4c5.7-2.7,15.1-4,29.1-4S58.9,1.3,64.6,4c3.7,1.8,6.3,2,31.7,2H124V20H77Z'))
							->setAttribute('transform', "scale({$scaleX},{$scaleY})")
					)
						->setAttribute('x', $this->AnchorFrontDistance - $anchorWidth/3)
						->setAttribute('y', $truckBackY - $anchorHeight)
				);

					
			break;
			
		}
	}
	
	public function getProfileHeight()
	{
		if ($this->trailer) {
			return $this->trailer->getProfileHeight();
		}
		return $this->_defaultHeight;
	}
	public function getProfileWidth()
	{
		return $this->FullLength;
	}
	
	public function setTrailer(VehicleSvg $vehicle)
	{
		$this->trailer = $vehicle;
	}
}
