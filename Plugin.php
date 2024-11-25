<?php
/**
 * 导出文章到 Jekyll
 * 
 * @package ExportToJekyll
 * @author Sun
 * @version 1.0.0
 * @link https://www.imsun.org
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class ExportToJekyll_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法
     */
    public static function activate()
    {
        Helper::addPanel(1, 'ExportToJekyll/panel.php', _t('导出到Jekyll'), _t('导出到Jekyll'), 'administrator');
        Helper::addAction('export-jekyll', 'ExportToJekyll_Action');
        return _t('插件已经激活，请设置导出路径');
    }

    /**
     * 禁用插件方法
     */
    public static function deactivate()
    {
        Helper::removePanel(1, 'ExportToJekyll/panel.php');
        Helper::removeAction('export-jekyll');
        return _t('插件已被禁用');
    }

    /**
     * 获取插件配置面板
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $exportPath = new Typecho_Widget_Helper_Form_Element_Text(
            'exportPath', 
            null,
            __TYPECHO_ROOT_DIR__ . '/usr/plugins/ExportToJekyll/export/',
            _t('导出路径'),
            _t('请输入导出文件保存的路径，默认为插件目录下的export文件夹')
        );
        $form->addInput($exportPath);
    }

    /**
     * 个人用户的配置面板
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
}