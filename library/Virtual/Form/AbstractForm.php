<?php
/**
 * Created by PhpStorm.
 * User: ZhongHao-Zh
 * Date: 2020/1/11
 * Time: 15:26
 */

namespace Library\Virtual\Form;

/**
 * Class AbstractForm
 * @package Library\Virtual\Form
 */
abstract class AbstractForm
{
    /**
     * @var array $formRule
     */
    public $formRule = [];

    /**
     * AbstractForm constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $needParams = get_object_vars($this);
        foreach ($needParams as $key => $value) {
            if (!isset($data[$key]) && $value === NULL) {
                $this->$key = '';
            } else {
                if (isset($this->formRule[$key])) {
                    $this->$key = ($this->formRule[$key]($data)) ?: '';
                } else {
                    $this->$key = $data[$key] ?? $value;
                }
            }
        }
    }
}