/**
 * файл с обработчиком для поля редактора (взаимодействие с сокетами) + вспомогательные функции для отправки diff
 */
function getCommonPrefixLength(text1, text2) {
    if (text1.charAt(0) != text2.charAt(0)) {
        return 0;
    }

    var min = 0;
    var max = Math.min(text1.length, text2.length);
    var mid = max;
    var start = 0;
    while (min < mid) {
        if (text1.substring(start, mid) ==
            text2.substring(start, mid)) {
            min = mid;
            start = min;
        } else {
            max = mid;
        }
        mid = Math.floor((max - min) / 2 + min);
    }
    return mid;
}
function getCommonSuffixLength(text1, text2) {
    var min = 0;
    var max = Math.min(text1.length, text2.length);
    var mid = max;
    var end = 0;
    while (min < mid) {
        if (text1.substring(text1.length - mid, text1.length - end) ==
            text2.substring(text2.length - mid, text2.length - end)) {
            min = mid;
            end = min;
        } else {
            max = mid;
        }
        mid = Math.floor((max - min) / 2 + min);
    }
    return mid;
}
function checkDiff(prevText, currText) {
    var diffs = [];
    if (prevText == currText) {
        return diffs;
    }
    if (!prevText) {
        diffs.push({type: 'add', idx: 0, diff: currText});
        return diffs;
    }
    if (!currText) {
        diffs.push({type: 'remove', idx: 0, diff: prevText});
        return diffs;
    }
    var prefixLength = getCommonPrefixLength(prevText, currText);
    prevText = prevText.substring(prefixLength);
    currText = currText.substring(prefixLength);
    var suffixLength = getCommonSuffixLength(prevText, currText);
    prevText = prevText.substring(0, prevText.length - suffixLength);
    currText = currText.substring(0, currText.length - suffixLength);

    if (prevText == currText) {
        return diffs;
    }
    if (!prevText) {
        diffs.push({type: 'add', idx: prefixLength, diff: currText});
        return diffs;
    }
    if (!currText) {
        diffs.push({type: 'remove', idx: prefixLength, diff: prevText});
        return diffs;
    }

    var longtext = prevText > currText ? prevText : currText;
    var shorttext = prevText > currText ? currText : prevText;
    var index = longtext.indexOf(shorttext);
    if (index != -1) {
        diffs.push({type: prevText < currText ? 'add' : 'remove', idx: prefixLength, diff: longtext.substring(0, index)});
        diffs.push({type: prevText < currText ? 'add' : 'remove', idx: prefixLength + index + shorttext.length, diff: longtext.substring(index + shorttext.length)});
        return diffs;
    }

    diffs.push({type: 'remove', idx: prefixLength, diff: prevText});
    diffs.push({type: 'add', idx: prefixLength, diff: currText});
    return diffs;
}

editorSM = {
    lastState: '',
    observer: null,
    object: null,
    init: function(object) {
        editorSM.object = object;
        // обработчик вебсокета и редактора ссылаются друг на друга для обмена сообщениями
        editorSM.observer = websocketHandler;
        websocketHandler.observer = editorSM;
        this.observer.init();
    },
    onload: function(text) {
        if (!this.object) return;
        this.object.removeAttr('disabled').val(text);
        this.lastState = text;
    },
    notify: function () {
        if (!this.object) return;
        var state = this.object.val();
        // compare with state after latest change and get diffs
        var diffs = checkDiff(this.lastState, state);
        // if have diffs, send it to observer (websocket)
        if (diffs.length) {
            this.observer.update(diffs);
        }
        this.lastState = state;
    },
    update: function (patch) {
        var text = this.object.val();
        for (var i = 0; i < patch.length; i++) {
            var type = patch[i].type;
            var idx = patch[i].idx;
            var diff = patch[i].diff;
            switch (type) {
                case 'add':
                    text = text.slice(0, idx) + diff + text.slice(idx);
                    break;
                case 'remove':
                    if (text.substr(idx, diff.length) == diff) {
                        text = text.slice(0, idx) + text.slice(idx + diff.length)
                    }
                    break;
            }
        }
        this.object.val(text);
    },
};