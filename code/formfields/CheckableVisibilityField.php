<?php
/**
 * A wrapper around a field to add a checkbox to optionally mark it as visible.
 *
 * @package    silverstripe-memberprofiles
 * @subpackage formfields
 */
class CheckableVisibilityField extends FormField {

	protected $child, $checkbox, $alwaysVisible = false;

	/**
	 * @param FormField $child
	 */
	public function __construct($child) {
		parent::__construct($child->getName());

		$this->child    = $child;
		$this->checkbox = new CheckboxField("Visible[{$this->name}]", '');
	}

	/**
	 * @return FormField
	 */
	public function getChild() {
		return $this->child;
	}

	/**
	 * @return CheckboxField
	 */
	public function getCheckbox() {
		return $this->checkbox;
	}

	public function makeAlwaysVisible() {
		$this->alwaysVisible = true;
		$this->checkbox->setValue(true);
		$this->checkbox = $this->checkbox->performDisabledTransformation();
	}

	public function setValue($value, $data = array()) {
		$this->child->setValue($value);

		if ($this->alwaysVisible) {
			$this->checkbox->setValue(true);
		} elseif (is_array($data)) {
			$this->checkbox->setValue((
				isset($data['Visible'][$this->name]) && $data['Visible'][$this->name]
			));
		} else {
			$this->checkbox->setValue(in_array(
				$this->name, $data->getPublicFields()
			));
		}

		return $this;
	}

	public function saveInto(DataObjectInterface $record) {
		$child = clone $this->child;
		$child->setName($this->name);
		$child->saveInto($record);

		$public = $record->getPublicFields();

		if ($this->checkbox->dataValue()) {
			$public = array_merge($public, array($this->name));
		} else {
			$public = array_diff($public, array($this->name));
		}

		$record->setPublicFields($public);
	}

	public function validate($validator) {
		return $this->child->validate($validator);
	}

	public function Value() {
		return $this->child->Value();
	}

	public function dataValue() {
		return $this->child->dataValue();
	}

	public function setForm($form) {
		$this->child->setForm($form);
		$this->checkbox->setForm($form);

		if($this->child instanceof FileField) {
			$form->setEncType(Form::ENC_TYPE_MULTIPART);
		}

		return parent::setForm($form);
	}

	public function Field($properties = array()) {
		return $this->child->Field() . ' ' . $this->checkbox->Field();
	}

	public function Title() {
		return $this->child->Title();
	}


	/**
	 * Returns a readonly version of this field
	 */
	public function performReadonlyTransformation() {
		$copy = $this->child->castedCopy('ReadonlyField');
		if ($this->child->hasMethod('getSource')) {
			//e.g. DropdownField: set the title of the current selection
			$source = $this->child->getSource();
			$selection = $source[$this->child->Value()];
			$copy->setValue($selection);
		}
		$copy->setReadonly(true);
		return $copy;
	}

	/**
	 * Add a CSS-class to the formfield-container.
	 *
	 * @param $class String
	 */
	public function addExtraClass($class) {
		$this->extraClasses[$class] = $class;
		$this->child->addExtraClass($class);
		return $this;
	}

	/**
	 * Remove a CSS-class from the formfield-container.
	 *
	 * @param $class String
	 */
	public function removeExtraClass($class) {
		$pos = array_search($class, $this->extraClasses);
		if($pos !== false) unset($this->extraClasses[$pos]);

		$this->child->removeExtraClass($class);

		return $this;
	}

	public function setAttribute($name, $value) {
		return $this->child->setAttribute($name,$value);
	}

}
