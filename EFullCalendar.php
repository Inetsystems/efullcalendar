<?php

class EFullCalendar extends CWidget
{
    /**
     * @var string Google's calendar URL.
     */
    public $googleCalendarUrl;

    /**
     * @var string Theme's CSS file.
     */
    public $themeCssFile;

    /**
	 * @var string the root URL that contains all JUI theme folders.
	 * If this property is not set (default), Yii will publish the JUI package included in the zii release and use
	 * that to infer the root theme URL. You should set this property if you intend to use
	 * a theme that is not found in the JUI package included in zii.
	 * Note that under this URL, there must be a directory whose name is specified by {@link theme}.
	 * Do not append any slash character to the URL.
	 */
	public $themeUrl;
	/**
	 * @var string the JUI theme name. Defaults to 'base'. Make sure that under {@link themeUrl} there
	 * is a directory whose name is the same as this property value (case-sensitive).
	 */
	public $theme='base';
    /**
	 * @var mixed the theme CSS file name. Defaults to 'jquery-ui.css'.
	 * Note the file must exist under the URL specified by {@link themeUrl}/{@link theme}.
	 * If you need to include multiple theme CSS files (e.g. during development, you want to include individual
	 * plugin CSS files), you may set this property as an array of the CSS file names.
	 * This property can also be set as false, which means the widget will not include any theme CSS file,
	 * and it is your responsibility to explicitly include it somewhere else.
	 */
	public $cssFile='jquery-ui.css';
    /**
     * @var array FullCalendar's options.
     */
    public $options=array();

    /**
     * @var array HTML options.
     */
    public $htmlOptions=array();

    /**
     * @var bool
     */
    public $loadPrintCss=false;

    /**
     * @var string Language code as ./locale/<code>.php file
     */
    public $lang;
    
    /**
     * @var string PHP file extension. Default is php.
     */
    public $ext='php';

    /**
     * Runs the widget.
     */
    public function run()
    {
        if ($this->lang) {
            $this->registerLocale($this->getLanguageFilePath());
        }

        $this->registerFiles();

        echo $this->showOutput();
    }

    /**
     * Registers language file.
     *
     * @param $langFile string Path to the language file.
     */
    protected function registerLocale($langFile)
    {
        if (file_exists($langFile)) {
            $this->options=CMap::mergeArray($this->options, include($langFile));
        } else {
            Yii::log(sprintf('EFullCalendar language file %s is missing', $langFile), CLogger::LEVEL_WARNING);
        }
    }

    /**
     * Gets default language file.
     */
    protected function getLanguageFilePath()
    {
        return dirname(__FILE__).'/locale/'.$this->lang.'.'.$this->ext;
    }

    public function init() {
        parent::init();

        $this->options['theme'] = true;
        $cs = Yii::app()->getClientScript();
        if ($this->themeUrl === null)
            $this->themeUrl = $cs->getCoreScriptUrl() . '/jui/css';

        if (is_string($this->cssFile))
            $cs->registerCssFile($this->themeUrl . '/' . $this->theme . '/' . $this->cssFile);
        elseif (is_array($this->cssFile)) {
            foreach ($this->cssFile as $cssFile)
                $cs->registerCssFile($this->themeUrl . '/' . $this->theme . '/' . $cssFile);
        }

    }

    /**
     * Registers assets.
     */
    protected function registerFiles()
    {
        $assetsDir=(defined(__DIR__) ? __DIR__ : dirname(__FILE__)).'/assets';
        $assets=Yii::app()->assetManager->publish($assetsDir);

        $cs=Yii::app()->clientScript;
        $cs->registerCoreScript('jquery');
        $cs->registerCoreScript('jquery.ui');

        $ext=defined('YII_DEBUG') && YII_DEBUG ? 'js' : 'min.js';
        $cs->registerScriptFile($assets.'/fullcalendar/fullcalendar.'.$ext);
        $cs->registerCssFile($assets.'/fullcalendar/fullcalendar.css');

        if ($this->loadPrintCss) {
            $cs->registerCssFile($assets.'/fullcalendar/fullcalendar.print.css');
        }
        if ($this->googleCalendarUrl) {
            $cs->registerScriptFile($assets.'/fullcalendar/gcal.js');
            $this->options['events']=$this->googleCalendarUrl;
        }

        $js='$("#'.$this->id.'").fullCalendar('.CJavaScript::encode($this->options).');';
        $cs->registerScript(__CLASS__.'#'.$this->id, $js, CClientScript::POS_READY);
    }

    /**
     * Returns the html output.
     *
     * @return string Html output
     */
    protected function showOutput()
    {
        if (! isset($this->htmlOptions['id']))
            $this->htmlOptions['id']=$this->id;

        return CHtml::tag('div', $this->htmlOptions,'');
    }
}
