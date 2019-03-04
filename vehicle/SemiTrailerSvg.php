<?php
namespace ils\svg\vehicle;

use SVG\SVG;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Shapes\SVGCircle;
use SVG\Nodes\Shapes\SVGPolygon;
use SVG\Nodes\Shapes\SVGPath;
use SVG\Nodes\Structures\SVGGroup;
use SVG\Nodes\Structures\SVGDocumentFragment;

	
class SemiTrailerSvg extends VehicleSvg{
	
	const CLEARANCE = 1000;
	const WHEEL = 450;
	
	
	public function getSizes()
	{
		$AxleBackWheelDistance = isset($this->AxleBackWheelDistance) ? $this->AxleBackWheelDistance : 1000;
		// ['title', 'size', 'orientation'(, 'startOffset', 'real_length')]
		return [
			'FullLength'=>['FullLength', $this->FullLength, VehicleFootnotesSvg::SIZE_FOOTNOTE_HORIZONTAL, 0, $this->Length],
			'FullHeight'=>['FullHeight', $this->FullHeight, VehicleFootnotesSvg::SIZE_FOOTNOTE_VERTICAL, 0, $this->getProfileHeight()],
            'FullWidth'=>['FullWidth', $this->FullWidth, VehicleFootnotesSvg::SIZE_FOOTNOTE_HORIZONTAL, 0, $this->getBackWidth()],
            'Length'=>['Length', $this->Length, VehicleFootnotesSvg::SIZE_FOOTNOTE_HORIZONTAL],
			'Height'=>['Height', $this->Height, VehicleFootnotesSvg::SIZE_FOOTNOTE_VERTICAL, 0, $this->Height],
            'Width'=>['Width', $this->Width, VehicleFootnotesSvg::SIZE_FOOTNOTE_HORIZONTAL, 0, $this->getBackWidth()],
			'AxleBackDistance'=>['AxleBackDistance', $this->AxleBackDistance, VehicleFootnotesSvg::SIZE_FOOTNOTE_HORIZONTAL, $this->Length-$this->AxleBackDistance],
			'AxleBackWheelDistance'=>['AxleBackWheelDistance', $AxleBackWheelDistance, VehicleFootnotesSvg::SIZE_FOOTNOTE_HORIZONTAL, $this->Length-$this->AxleBackDistance - ($AxleBackWheelDistance * $this->AxleBackWheelsCount) / 2 + $AxleBackWheelDistance/2],
			'AnchorFrontDistance'=>['AnchorFrontDistance', $this->AnchorFrontDistance, VehicleFootnotesSvg::SIZE_FOOTNOTE_HORIZONTAL],
		];
	}
	
	public function getProfile()
	{
		$container = new SVGGroup;
		$mudguardWidth = 100;
		$trailerBaseHeight = 300;
		
		$trailerX = 0;
		$trailerY = 0;
		
		// trailer base
		$trailerBaseX = $this->Length/3 + $trailerX;
		$trailerBaseY = $trailerY + $this->Height;
		$trailerBaseW = $this->Length/3*2;
		
		$container->addChild(
			(new SVGRect($trailerBaseX, $trailerBaseY, $trailerBaseW, $trailerBaseHeight))
				->setAttribute('id', 'SemiTrailerBase')
		);
		
		$axleBaseX = $trailerX + $this->Length - $this->AxleBackDistance -
			$this->AxleBackWheelsCount * ($this->AxleBackWheelDistance ? $this->AxleBackWheelDistance : 1000)/2;
		$axleBaseY = $trailerY + $this->Height;
		$axleBaseW = ($this->AxleBackWheelDistance ? $this->AxleBackWheelDistance : 1000) * $this->AxleBackWheelsCount;
		$container->addChild(
			(new SVGRect($axleBaseX, $axleBaseY, $axleBaseW, self::WHEEL))
				->setAttribute('id', 'SemiTrailerBackAxleBase')
		);
		
		
		// mudguard
		$mudguardX = $axleBaseX + $axleBaseW;
		$container->addChild(
			(new SVGRect($mudguardX, $axleBaseY+self::WHEEL/2, $mudguardWidth, self::WHEEL))
				->setAttribute('id', 'SemiTrailerMudguard')
		);
		
		// spare wheel
		if (($axleBaseX + $axleBaseW + 1000 + self::WHEEL*2) < $trailerX + $this->Length) {
			$spareX = $mudguardX + 700;
			$spareY = $trailerBaseY + $trailerBaseHeight + 30;
			$container->addChild(
				(new SVGRect($spareX, $spareY, self::WHEEL*2, 350))
					->setAttribute('id', 'SemiTrailerSpareWheel')
			);
			
			$container->addChild(
				(new SVGRect($spareX-100, $spareY-30, 100, 400))
					->setAttribute('id', 'SemiTrailerSpareWheelLeftRack')
			);
			$container->addChild(
				(new SVGRect($spareX+self::WHEEL*2, $spareY-30, 100, 400))
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
		$axleBackX = $trailerX + $this->Length - $this->AxleBackDistance -
			$this->AxleBackWheelsCount * ($this->AxleBackWheelDistance ? $this->AxleBackWheelDistance : 1000)/2;
		$axleBackY = $this->getProfileHeight() - self::WHEEL;
		for ($i = 0; $i < $this->AxleBackWheelsCount; $i++) {
			$wheelCenterX = $axleBackX + ($this->AxleBackWheelDistance ? $this->AxleBackWheelDistance : 1000) * ($i+0.5);
			$container->addChild(
				(new SVGCircle($wheelCenterX, $axleBackY, self::WHEEL))
					->setAttribute('id', 'SemiTrailerBackWheel'.$i)
					->setAttribute('class', 'SemiTrailerBackWheel Wheel')
					->setAttribute('max_tonnage', $this->AxleBackWheelMaxTonnage)
					->setAttribute('vehicleType', 'semiTrailer')
					->setAttribute('axle', 'back')
					->setAttribute('num', $i)
			);
			$container->addChild(
				(new SVGCircle($wheelCenterX, $axleBackY, self::WHEEL-200))
					->setStyle('fill', '#fff')
					->setAttribute('id', 'SemiTrailerBackRim'.$i)
					->setAttribute('class', 'SemiTrailerBackWheelRim')
			);
		}
		
		// trailer main
		$container->addChild(
			(new SVGRect($trailerX, $trailerY, $this->Length, $this->Height))
				->setStyle('fill', '#fff')
				->setStyle('stroke', '#000')
				->setStyle('stroke-width', 50 . 'px')
				->setAttribute('id', 'SemiTrailerProfile')
				->setAttribute('data-length', $this->Length)
				->setAttribute('data-width', $this->Width)
				->setAttribute('data-height', $this->Height)
		);
		return (new SVGDocumentFragment)
			->addChild($container)
			->setAttribute('width', $this->getProfileWidth())
			->setAttribute('height', $this->getProfileHeight());
	
	}
	
	public function getBack()
	{
		$container = new SVGGroup;
		$mudguardWidth = 100;
		$trailerBaseHeight = 300;
		
		// trailer base + axle base
		
		$container->addChild(
			(new SVGRect(0, $this->Height, $this->Width, self::WHEEL))
				->setAttribute('id', 'SemiTrailerBase')
		);
		
		
		// spare wheel
		$axleBaseX = $this->Length - $this->AxleBackDistance -
			$this->AxleBackWheelsCount * ($this->AxleBackWheelDistance ? $this->AxleBackWheelDistance : 1000)/2;
		$axleBaseW = ($this->AxleBackWheelDistance ? $this->AxleBackWheelDistance : 1000) * $this->AxleBackWheelsCount;
		if (($axleBaseX + $axleBaseW + 1000 + self::WHEEL*2) < $this->Length) {
			$spareX = $this->Width / 2 - self::WHEEL;
			$spareY = $this->Height + $trailerBaseHeight + 30;
			$container->addChild(
				(new SVGRect($spareX, $spareY, self::WHEEL*2, 350))
					->setAttribute('id', 'SemiTrailerSpareWheel')
			);
			
			$container->addChild(
				(new SVGRect($spareX-100, $spareY-30, 100, 400))
					->setAttribute('id', 'SemiTrailerSpareWheelLeftRack')
			);
			$container->addChild(
				(new SVGRect($spareX+self::WHEEL*2, $spareY-30, 100, 400))
					->setAttribute('id', 'SemiTrailerSpareWheelRightRack')
			);
		}
		
		// wheels
		// left
		$container->addChild(
			(new SVGRect(0, $this->getBackHeight()-self::WHEEL*2, 350, self::WHEEL*2))
				->setAttribute('class', 'wheel')
		);
		$container->addChild(
			(new SVGRect(350 + 30, $this->getBackHeight()-self::WHEEL*2, 350, self::WHEEL*2))
				->setAttribute('class', 'wheel')
		);
		// right
		$container->addChild(
			(new SVGRect($this->Width-350*2-30, $this->getBackHeight()-self::WHEEL*2, 350, self::WHEEL*2))
				->setAttribute('class', 'wheel')
		);
		$container->addChild(
			(new SVGRect($this->Width-350, $this->getBackHeight()-self::WHEEL*2, 350, self::WHEEL*2))
				->setAttribute('class', 'wheel')
		);
		
		// mudguard
		$container->addChild(
			(new SVGRect(0, $this->Height+self::WHEEL/2, 350*2+30, self::WHEEL))
				->setAttribute('class', 'mudguard')
		);
		$container->addChild(
			(new SVGRect($this->Width-350*2-30, $this->Height+self::WHEEL/2, 350*2+30, self::WHEEL))
				->setAttribute('class', 'mudguard')
		);
		
		// trailer main
		$container->addChild(
			(new SVGRect(0, 0, $this->Width, $this->Height))
				->setStyle('fill', '#fff')
				->setStyle('stroke', '#000')
				->setStyle('stroke-width', 5/$this->scale . 'px')
				->setAttribute('id', 'SemiTrailerBack')
				->setAttribute('data-length', $this->Length)
				->setAttribute('data-width', $this->Width)
				->setAttribute('data-height', $this->Height)
		);
		return $container;
	}
	
	public function getProfileHeight()
	{
		return $this->Height + self::CLEARANCE;
	}
	
	public function getProfileWidth()
	{
		 return $this->Length;
	}
	
	public function getBackHeight()
	{
		return $this->Height + self::CLEARANCE;
	}
	
	public function getBackWidth()
	{
		 return $this->Width;
	}
	
}
