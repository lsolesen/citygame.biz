<?php
require_once dirname(__FILE__) . '/config.local.php';
require_once 'konstrukt/konstrukt.inc.php';
require_once 'bucket.inc.php';
require_once 'Ilib/ClassLoader.php';

function create_container()
{
    $factory = new ApplicationFactory();
    $container = new bucket_Container($factory);
    $factory->template_dir = realpath(dirname(__FILE__) . '/templates');
    return $container;
}

class ApplicationFactory
{
    public $template_dir;
    public $pdo_dsn;
    public $pdo_username;
    public $pdo_password;

    function new_k_TemplateFactory($c)
    {
        return new k_DefaultTemplateFactory($this->template_dir);
    }
}

class Citygame_Component_Root extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    protected function map($name)
    {
        if ($name == "game") {
            return 'Citygame_Component_Game';
        }
    }

    function execute()
    {
        return $this->wrap(parent::execute());
    }

    function wrapHtml($content)
    {
        $tpl = $this->template->create('wrapper');
        $data = array(
          'content' => $content
        );
        return $tpl->render($this, $data);
    }

    function renderHtml()
    {
        $tpl = $this->template->create('target');
        return $tpl->render($this);
    }
}

class Citygame_Component_Game extends k_Component
{
    protected $template;

    function __construct(k_TemplateFactory $template)
    {
        $this->template = $template;
    }

    function renderHtml()
    {
        if ($this->query('game')) {
            return new k_SeeOther('http://85.255.207.58/TheTarget3/CurentView.aspx?a=' . intval($this->query('game')));
        }

        $tpl = $this->template->create('game');
        return $tpl->render($this);
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME']) == __FILE__) {
    k()
    ->setComponentCreator(new k_InjectorAdapter(create_container()))
    ->run('Citygame_Component_Root')->out();
}
