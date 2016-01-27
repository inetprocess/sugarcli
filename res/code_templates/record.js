({
    // Created by sugarcli
    extendsFrom: 'RecordView',
    initialize: function (options) {
        app.view.invokeParent(this, {type: 'view', name: 'record', method: 'initialize', args:[options]});

        this.context.on('button:[[name]]:click', this.[[name]], this);
    },
    [[name]]: function() {
        console.log('Hi');
    }
})
