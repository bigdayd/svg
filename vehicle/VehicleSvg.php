<?php
namespace ils\svg\vehicle;

use SVG\Nodes\Structures\SVGDocumentFragment;
use SVG\Nodes\Structures\SVGGroup;

	
abstract class VehicleSvg extends SvgImageItem{
	
	public $_scale = 1;
	public $FullLength;
	public $FullWidth;
	public $FullHeight;
	public $Length;
	public $Width;
	public $Height;
	public $Tonnage;
	public $Mass;
	public $MassCenterDistance;
	public $AxleFrontDistance;
	public $AxleFrontWheelsCount;
	public $AxleFrontWheelDistance;
	public $AxleFrontWheelMaxTonnage;
	public $AxleFrontWheelEmptyTonnage;
	public $AxleBackDistance;
	public $AxleBackWheelsCount;
	public $AxleBackWheelDistance;
	public $AxleBackWheelMaxTonnage;
	public $AxleBackWheelEmptyTonnage;
	public $AnchorHeight;
	public $AnchorFrontDistance;
	public $AnchorEmptyTonnage;
	
	const TYPE_ROW_TRUCK = 0;
	const TYPE_TRUCK = 1;
	const TYPE_FREIGHT_CAR = 2;
	const TYPE_LORRY = 3;
	const TYPE_SEMI_TRAILER = 20;
	const TYPE_DUMP_TRAILER = 21;
	const TYPE_CHASSIS = 22;
	const TYPE_CONTAINER = 23;
	
	const PROFILE_SIDE = 0;
	const TOP_SIDE = 1;
	const BACK_SIDE = 2;
	
	public function __construct($data = [])
	{
		foreach ($data as $k=>$v) {
			$this->$k = $v;
		}
	}
	
	public static function create($data)
	{
		switch ($data['VehicleType']) {
			case self::TYPE_ROW_TRUCK:
				return new RowTruckSvg($data);
			break;
			case self::TYPE_TRUCK:
			break;
			case self::TYPE_FREIGHT_CAR:
			break;
			case self::TYPE_LORRY:
			break;
			
			
			case self::TYPE_SEMI_TRAILER:
				return new SemiTrailerSvg($data);
			break;
			case self::TYPE_DUMP_TRAILER:
			break;
			case self::TYPE_CHASSIS:
			break;
			case self::TYPE_CONTAINER:
			break;
		}
	}
	
	public function getProfile()
	{
		$container = new SVGDocumentFragment;
		return $container;
	}
	
	public function getTop()
	{
		$container = new SVGGroup;
		return $container;
	}
	
	public function getBack()
	{
		$container = new SVGGroup;
		return $container;
	}
	
	public function getSizes()
	{
		// ['title', 'size', 'orientation'(, 'startOffset', 'real_length')]
		return [];
	}
	
	public function getSvg($type=null)
	{
		$g = new SVGGroup;
		switch ($type) {
			case VehicleSvg::PROFILE_SIDE:
				$g->addChild($this->getProfile());
			break;
			case VehicleSvg::TOP_SIDE:
				$g->addChild($this->getTop());
			break;
			case VehicleSvg::BACK_SIDE:
				$g->addChild($this->getBack());
			break;
		}
		$g->setStyle('transform', "scale({$this->scale})");
		return (new SVGDocumentFragment)->addChild($g);
	}
	
	public function getWidth($type=null)
	{
		switch ($type) {
			case self::PROFILE_SIDE:
				return $this->getProfileWidth();
			break;
			case self::TOP_SIDE:
				return $this->getTopWidth();
			break;
			case self::BACK_SIDE:
				return $this->getBackWidth();
			break;
		}
		return $this->getProfileHeight();
	}
	
	public function getHeight($type=null)
	{
		switch ($type) {
			case self::PROFILE_SIDE:
				return $this->getProfileHeight();
			break;
			case self::TOP_SIDE:
				return $this->getTopHeight();
			break;
			case self::BACK_SIDE:
				return $this->getBackHeight();
			break;
		}
		return $this->getProfileHeight();
	}
}
