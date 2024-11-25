<?php
include 'header.php';
include 'menu.php';
?>

<div class="main">
    <div class="body container">
        <div class="typecho-page-title">
            <h2>导出到Jekyll</h2>
        </div>
        <div class="row typecho-page-main" role="main">
            <div class="col-mb-12">
                <?php if (isset($request->success)): ?>
                <div class="message success">
                    <?php _e('文章导出成功！'); ?>
                </div>
                <?php endif; ?>
                
                <div class="typecho-list-operate clearfix">
                    <form action="<?php $security->index('/action/export-jekyll'); ?>" method="post">
                        
                        <div class="operate">
                            <input type="submit" class="btn primary" value="开始导出">
                        </div>
                        <p class="description" >
                            点击"开始导出"按钮将所有文章导出为Jekyll格式。<br/>
                            导出路径：<?php echo $options->plugin('ExportToJekyll')->exportPath; ?>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'footer.php';
?>