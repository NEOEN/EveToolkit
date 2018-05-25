<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 10.07.14
 * Time: 16:08
 */

namespace Core;

class HtmlView
{

    protected $twig, $data = [], $css, $htmlFormatted, $htmlMinified;

    public function __construct(Configuration $configuration, Javascript $js, Css $css, HttpRequest $httpRequest, Url $url)
    {
        $paths = $configuration->getAllParameterToGivenGroup('paths');
        $options = [
            'cache' => ((bool)$configuration->getParameter('cache', 'caching') ? PRIVATE_PATH.$paths['cacheTemplates'] : false),
            'debug' => (bool)$configuration->getParameter('debug', 'debug'),
        ];

        $loader = new \Twig_Loader_Filesystem(PRIVATE_PATH.$paths['projects'].PROJECT_NAME.DIRECTORY_SEPARATOR.$paths['templates']);
        $this->twig = new \Twig_Environment($loader, $options);
        if((bool)$configuration->getParameter('debug', 'debug'))
            $this->twig->addExtension(new \Twig_Extension_Debug());

        $this->assign('js', $js);
        $this->assign('css', $css);
        $this->assign('url', $url);

        $this->css = $css;
        $this->htmlFormatted = $httpRequest->getVar('get', 'htmlFormatted', 'bool', null, false);
        $this->htmlMinified = $httpRequest->getVar('get', 'htmlMinified', 'bool', null, $configuration->getParameter('html', 'minify'));
    }

    /**
     * Gibt das gerenderte Template zurück
     *
     * @param string $template
     * @param int $minifyDepth 0|1|2
     * @param $template
     * @param int $minifyDepth
     *
     * @return mixed|string
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function render($template, $minifyDepth = 0)
    {
        $html = $this->twig->render($template.'.html', $this->data);
        $html = $this->replacedCss($html);

        return \Modules\Minify\HtmlMinify::minify($html, $minifyDepth, $this->htmlFormatted, $this->htmlMinified);
    }

    /**
     * Gibt das gerenderte Template sofort aus.
     *
     * @param $template
     * @param int $minifyDepth 0|1|2
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function display($template, $minifyDepth = 0)
    {
        echo $this->render($template, $minifyDepth);
    }

    /**
     * Schreibt das zugehörige CSS in das übergebene Template
     *
     * @param $html
     * @return mixed
     */
    protected function replacedCss($html)
    {
        $replacedText = '(css.get())';
        if ($pos = strpos($html, $replacedText)) {
            return substr_replace($html, $this->css->get(), $pos, strlen($replacedText));
        }

        return $html;
    }

    /**
     * Nimmt ein key value entgegen was dem Template übergeben wird.
     *
     * @param $key
     * @param $value
     *
     * @throws \Exception
     */
    public function assign($key, $value)
    {
        if (!isset($this->data[$key])) {
            $this->data[$key] = $value;
        } else {
            throw new \Exception('View: Given variable '.$key.' already exists');
        }
    }

}