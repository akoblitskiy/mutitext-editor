<?php
/**
 * @var $websocketUrl
 */
?>
<div class="editor-panel">
    <div id="users_list"></div>
</div>
<textarea class="textbox" id="editor_box" disabled style="max-width: 100%; width: 100%;min-height: 250px">Loading...</textarea>
<div id="logs_box"></div>
<script>
    var wsUri = '<?= $websocketUrl ?>';
    window.onsocketopen = function () {
        var $editorBox = $("#editor_box");
        editorSM.init($editorBox);
        $editorBox.on('keyup mouseup paste', function () {
            editorSM.notify();
        });
        $editorBox.on('paste', function (e) { console.log(e); } )
    }
</script>