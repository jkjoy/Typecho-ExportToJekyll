<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class ExportToJekyll_Action extends Typecho_Widget implements Widget_Interface_Do
{
    private $user;
    private $exportedFiles = [];
    
    public function __construct($request, $response, $params = NULL)
    {
        parent::__construct($request, $response, $params);
        $this->user = Typecho_Widget::widget('Widget_User');
    }

    public function execute()
    {
        if (!$this->user->hasLogin()) {
            throw new Typecho_Widget_Exception(_t('必须登录后才能使用此功能'), 403);
        }
        
        if (!$this->user->pass('administrator', true)) {
            throw new Typecho_Widget_Exception(_t('只有管理员才能使用此功能'), 403);
        }
    }

    public function action()
    {
        $this->execute();
        
        $options = Helper::options()->plugin('ExportToJekyll');
        $outputDir = $options->exportPath;

        try {
            $count = $this->exportPosts($outputDir);
            
            // 设置成功消息，包含导出文件数量和路径
            $message = sprintf(
                _t('导出成功！共导出 %d 篇文章到：%s'),
                $count,
                $outputDir
            );
            
            if (!empty($this->exportedFiles)) {
                $message .= "\n\n" . _t('导出的文件：') . "\n- " . implode("\n- ", $this->exportedFiles);
            }
            
            // 将消息存储到 session 中
            $this->widget('Widget_Notice')->set($message, 'success');
            
        } catch (Exception $e) {
            $this->widget('Widget_Notice')->set(_t('导出失败：%s', $e->getMessage()), 'error');
        }

       // 在 Action.php 的 action() 方法中修改重定向代码
$this->response->redirect(Typecho_Common::url('extending.php?panel=ExportToJekyll%2Fpanel.php&success=1', Helper::options()->adminUrl));
    }

    private function exportPosts($outputDir)
    {
        if (!file_exists($outputDir)) {
            if (!mkdir($outputDir, 0755, true)) {
                throw new Exception(_t('无法创建导出目录'));
            }
        }

        if (!is_writable($outputDir)) {
            throw new Exception(_t('导出目录不可写'));
        }

        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        $posts = $db->fetchAll($db->select()->from($prefix . 'contents')
            ->where('type = ?', 'post')
            ->order('created', Typecho_Db::SORT_DESC));

        $count = 0;
        $this->exportedFiles = []; // 重置导出文件列表

        foreach ($posts as $post) {
            $post = Typecho_Widget::widget('Widget_Abstract_Contents')->filter($post);
            $slug = $post['slug'];
            $created = (int)$post['created'];
            $date = new DateTime("@$created");
            $date->setTimezone(new DateTimeZone('Asia/Shanghai'));
            $dateStr = $date->format('Y-m-d H:i:s P');
            $title = str_replace('"', '\"', $post['title']);
            $content = $post['text'];

            $categories = $this->getCategories($post['cid']);
            $tags = $this->getTags($post['cid']);

            $fileName = $date->format('Y-m-d') . '-' . $slug . '.md';
            $filePath = $outputDir . $fileName;

            $frontMatter = "---\n";
            $frontMatter .= "layout: post\n";
            $frontMatter .= "title: \"" . $title . "\"\n";
            $frontMatter .= "date: " . $dateStr . "\n";
            if (!empty($categories)) {
                $frontMatter .= "categories: [" . implode(', ', array_map(function($cat) {
                    return '"' . str_replace('"', '\"', $cat) . '"';
                }, $categories)) . "]\n";
            }
            if (!empty($tags)) {
                $frontMatter .= "tags: [" . implode(', ', array_map(function($tag) {
                    return '"' . str_replace('"', '\"', $tag) . '"';
                }, $tags)) . "]\n";
            }
            $frontMatter .= "---\n\n";

            if (false === file_put_contents($filePath, $frontMatter . $content)) {
                throw new Exception(_t('无法写入文件：%s', $fileName));
            }

            $this->exportedFiles[] = $fileName; // 记录成功导出的文件
            $count++;
        }

        return $count;
    }

    private function getCategories($cid)
    {
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        $categories = $db->fetchAll($db->select()->from($prefix . 'relationships')
            ->join($prefix . 'metas', '`' . $prefix . 'relationships`.`mid` = `' . $prefix . 'metas`.`mid`')
            ->where('`' . $prefix . 'relationships`.`cid` = ?', $cid)
            ->where('`' . $prefix . 'metas`.`type` = ?', 'category'));

        return array_column($categories, 'name');
    }

    private function getTags($cid)
    {
        $db = Typecho_Db::get();
        $prefix = $db->getPrefix();
        $tags = $db->fetchAll($db->select()->from($prefix . 'relationships')
            ->join($prefix . 'metas', '`' . $prefix . 'relationships`.`mid` = `' . $prefix . 'metas`.`mid`')
            ->where('`' . $prefix . 'relationships`.`cid` = ?', $cid)
            ->where('`' . $prefix . 'metas`.`type` = ?', 'tag'));

        return array_column($tags, 'name');
    }
}