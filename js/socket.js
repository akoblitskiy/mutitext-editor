/**
 * здесь происходит соединение и работа с сокетами в текстовом редакторе
 */
var logsBox = jQuery('#logs_box');

// create a new WebSocket object
websocket = new WebSocket(wsUri);
websocket.onopen = function(ev) { // connection is open
    logsBox.append('<div class="system_msg" style="color:#bbbbbb">Welcome to my "Multitext"!</div>'); //notify user
    window.onsocketopen();
};
// Message received from server
websocket.onmessage = function(ev) {
    var response = JSON.parse(ev.data); // server sends Json data

    var res_type = response.type; //response type
    var data = response.data; //response data
    switch(res_type){
        case 'load':
            logsBox.append('<div><span class="message" style="color:#0b2e13">File loaded</span></div>');
            websocketHandler.onload(data);
            break;
        case 'patch':
            logsBox.append('<div><span class="message" style="color:#0b2e13">Patch:</span>' + JSON.stringify(data) + '</div>');
            websocketHandler.notify(data);
            break;
        case 'system':
            logsBox.append('<div style="color:#1f1f1f">' + JSON.stringify(data) + '</div>');
            break;
    }
};

websocket.onerror = function(ev){ logsBox.append('<div style="color:#351412;font-weight: bold;">Error Occurred - ' + ev.data + '</div>'); };
websocket.onclose  = function(ev){ logsBox.append('<div class="system_msg">Connection Closed</div>'); };

websocketHandler = {
    observer: null,
    init: function() {
        var message = {
            type: 'load',
            data: ''
        };
        websocket.send(JSON.stringify(message))
    },
    onload: function(text) {
        this.observer.onload(text);
    },
    notify: function (patches) {
        // send patches to editor state machine
        this.observer.update(patches);
    },
    update: function (diffs) {
        // Pack diffs message and send to server
        var message = {
            type: 'diff',
            data: diffs
        };
        websocket.send(JSON.stringify(message))
    }
};