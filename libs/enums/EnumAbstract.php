<?php

//namespace smartyphp\core\enums;

/**
 * 枚举抽象类
 * @author SmartPower
 * @since 2018/3/14/
 * @version 1.0
 */
abstract class EnumAbstract {

	/**
	 * Constant with default value for creating enum object
	 */
	const __default = null;
	 
	private $value;
	 
	private $strict;
	 
	private static $constants = array();
	 
	/**
	 * Returns list of all defined constants in enum class.
	 * Constants value are enum values.
	 *
	 * @param bool $includeDefault If true, default value is included into return
	 * @return array Array with constant values
	 */
	public function getConstList($includeDefault = false) {
		 
		$class = get_class($this);
		 
		$r = new ReflectionClass($class);
		$constants = $r->getConstants();
		return $constants;
	}
	 
	/**
	 * Creates new enum object. If child class overrides __construct(),
	 * it is required to call parent::__construct() in order for this
	 * class to work as expected.
	 *
	 * @param mixed $initialValue Any value that is exists in defined constants
	 * @param bool $strict If set to true, type and value must be equal
	 * @throws UnexpectedValueException If value is not valid enum value
	 */
	public function __construct($initialValue = null, $strict = true) {
		 
		$class = get_class($this);
		 
		if (!array_key_exists($class, self::$constants)) {
			self::populateConstants();
		}
		 
		if ($initialValue === null) {
			$initialValue = self::$constants[$class]["__default"];
		}
		 
		$temp = self::$constants[$class];
		 
		if (!in_array($initialValue, $temp, $strict)) {
			throw new UnexpectedValueException("Value is not in enum " . $class);
		}
		 
		$this->value = $initialValue;
		$this->strict = $strict;
	}
	 
	private function populateConstants() {
		 
		$class = get_class($this);
		 
		$r = new ReflectionClass($class);
		$constants = $r->getConstants();
		 
		self::$constants = array(
				$class => $constants
		);
	}
	 
	/**
	 * Returns string representation of an enum. Defaults to
	 * value casted to string.
	 *
	 * @return string String representation of this enum's value
	 */
	public function __toString() {
		return (string) $this->value;
	}
	 
	/**
	 * Checks if two enums are equal. Only value is checked, not class type also.
	 * If enum was created with $strict = true, then strict comparison applies
	 * here also.
	 *
	 * @return bool True if enums are equal
	 */
	public function equals($object) {
		if (!($object instanceof Enum)) {
			return false;
		}
		 
		return $this->strict ? ($this->value === $object->value)
		: ($this->value == $object->value);
	}
}