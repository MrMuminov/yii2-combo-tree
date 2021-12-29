<?php

namespace MrMuminov\ComboTree;

use yii\base\Model;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseInflector;
use yii\base\InvalidConfigException;

/**
 * Class ComboTree - widget for rendering combo tree select
 *
 * @author Bahriddin Mo'minov
 * @package MrMuminov\ComboTree
 *
 * <?= \MrMuminov\ComboTree\ComboTree::widget([
 *     'source' => [
 *         [
 *             'id' => 1,
 *             'title' => 'choice 1',
 *         ],
 *         [
 *             'id' => 2,
 *             'title' => 'choice 2',
 *         ],
 *         [
 *             'id' => 3,
 *             'title' => 'choice 2 1',
 *             'parent_id' => 2,
 *         ],
 *         [
 *             'id' => 4,
 *             'title' => 'choice 2 2',
 *             'parent_id' => 2,
 *         ],
 *         [
 *             'id' => 5,
 *             'title' => 'choice 2 3',
 *             'parent_id' => 2,
 *         ],
 *         [
 *             'id' => 6,
 *             'title' => 'choice 3',
 *         ],
 *     ]
 * ]); ?>
 */
class ComboTree extends Widget
{
    const PLUGIN_NAME = 'comboTree';
    /**
     * @var \yii\widgets\ActiveField active input field, which triggers this widget rendering.
     * This field will be automatically filled up in case widget instance is created via [[\yii\widgets\ActiveField::widget()]].
     * @since 2.0.0
     */
    public $field;
    /**
     * @var string the name of the jQuery plugin
     */
    public $source;
    /**
     * @var Model the data model that this widget is associated with.
     */
    public $model;
    /**
     * @var string the model attribute that this widget is associated with.
     */
    public $attribute;
    /**
     * @var string the input name. This must be set if [[model]] and [[attribute]] are not set.
     */
    public $name;
    /**
     * @var string the input value.
     */
    public $value;
    /**
     * @var array the HTML attributes for the input tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = [];

    /**
     * @var bool $multiple to allow multiple selection.
     */
    public $multiple = false;

    /**
     * @var array $pluginOptions the options for the underlying jQuery plugin.
     */
    public $pluginOptions = [];

    /**
     * @return void
     * @throws InvalidConfigException
     */
    public function init()
    {
        if ($this->name === null && !$this->hasModel()) {
            throw new InvalidConfigException("Either 'name', or 'model' and 'attribute' properties must be specified.");
        }
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->hasModel() ? Html::getInputId($this->model, $this->attribute) : $this->getId();
        }
        parent::init();
    }

    /**
     * @return bool whether this widget is associated with a data model.
     */
    protected function hasModel()
    {
        return $this->model instanceof Model && $this->attribute !== null;
    }

    /**
     * @return string|void the result of the widget execution to be outputted.
     */
    public function run()
    {
        $this->registerClientScript();
        echo $this->renderInputHtml();
    }

    private function registerClientScript()
    {
        $view = $this->getView();
        ComboTreeAsset::register($view);
        $id = BaseInflector::id2camel($this->options['id']);
        $pluginOptions = $this->pluginOptions;
        $this->source = $this->generateTree($this->source);
        $pluginOptions['source'] = $this->source;
        $pluginOptions['isMultiple'] = $this->multiple;
        $view->registerJsVar(self::PLUGIN_NAME . '_' . $id . '_config', $pluginOptions);
        $view->registerJs('$("#' . $this->options['id'] . '").' . self::PLUGIN_NAME . '(' . self::PLUGIN_NAME . '_' . $id . '_config);');
    }

    private function renderInputHtml()
    {
        return Html::activeInput('text', $this->model, $this->attribute, $this->options);
    }

    private function generateTree(&$source, $parentId = null){
        $tree = [];
        foreach ($source as $item) {
            $item['parent_id'] = $item['parent_id'] ?? null;
            if ($item['parent_id'] == $parentId) {
                $item['subs'] = $this->generateTree($source, $item['id']);
                $tree[] = $item;
            }
        }
        return $tree;
    }
}
