/**
 * Loads the Contexts panel
 *
 * @class MODx.panel.Contexts
 * @extends MODx.FormPanel
 * @param {Object} config An object of configuration options
 * @xtype modx-panel-contexts
 */
MODx.panel.Contexts = function(config = {}) {
    Ext.applyIf(config, {
        id: 'modx-panel-contexts',
        cls: 'container',
        bodyStyle: '',
        defaults: {
            collapsible: false,
            autoHeight: true
        },
        items: [{
            html: _('contexts'),
            id: 'modx-contexts-header',
            xtype: 'modx-header'
        }, MODx.getPageStructure([{
            title: _('contexts'),
            layout: 'form',
            items: [{
                html: `<p>${_('context_management_message')}</p>`,
                xtype: 'modx-description'
            }, {
                xtype: 'modx-grid-contexts',
                cls: 'main-wrapper',
                preventRender: true
            }]
        }])]
    });
    MODx.panel.Contexts.superclass.constructor.call(this, config);
};
Ext.extend(MODx.panel.Contexts, MODx.FormPanel);
Ext.reg('modx-panel-contexts', MODx.panel.Contexts);

/**
 * Loads a grid of modContexts.
 *
 * @class MODx.grid.Context
 * @extends MODx.grid.Grid
 * @param {Object} config An object of configuration properties
 * @xtype modx-grid-contexts
 */
MODx.grid.Context = function(config = {}) {
    Ext.applyIf(config, {
        title: _('contexts'),
        id: 'modx-grid-context',
        url: MODx.config.connector_url,
        baseParams: {
            action: 'Context/GetList'
        },
        fields: [
            'key',
            'name',
            'description',
            'perm',
            'rank'
        ],
        paging: true,
        autosave: true,
        save_action: 'Context/UpdateFromGrid',
        remoteSort: true,
        primaryKey: 'key',
        columns: [{
            header: _('key'),
            dataIndex: 'key',
            width: 100,
            sortable: true
        }, {
            header: _('name'),
            dataIndex: 'name',
            width: 150,
            sortable: true,
            editor: { xtype: 'textfield' },
            renderer: {
                fn: function(v, md, record) {
                    return this.renderLink(v, {
                        href: `?a=context/update&key=${record.data.key}`
                    });
                },
                scope: this
            }
        }, {
            header: _('description'),
            dataIndex: 'description',
            width: 575,
            sortable: false,
            editor: { xtype: 'textarea' }
        }, {
            header: _('rank'),
            dataIndex: 'rank',
            width: 100,
            sortable: true,
            editor: { xtype: 'numberfield' }
        }],
        tbar: [
            {
                text: _('create'),
                cls: 'primary-button',
                handler: this.create,
                scope: this
            },
            '->',
            this.getQueryFilterField(),
            this.getClearFiltersButton()
        ]
    });
    MODx.grid.Context.superclass.constructor.call(this, config);

    this.on({
        afterAutoSave: function(response) {
            if (response.eventData.value !== response.eventData.originalValue) {
                const
                    resourceTree = Ext.getCmp('modx-resource-tree'),
                    contextNodeId = `${response.object.key}_0`,
                    contextRootNode = resourceTree.root.findChild('id', contextNodeId)
                ;
                if (resourceTree && resourceTree.rendered) {
                    switch (response.eventData.field) {
                        case 'name':
                            contextRootNode.setText(response.eventData.value);
                            break;
                        case 'description':
                            contextRootNode.setTooltip(response.eventData.value);
                            break;
                        case 'rank':
                            resourceTree.refresh();
                            break;
                        // no default
                    }
                }
            }
        }
    });
};
Ext.extend(MODx.grid.Context, MODx.grid.Grid, {
    getMenu: function() {
        const r = this.getSelectionModel().getSelected(),
              p = r.data.perm,
              m = [];
        if (p.indexOf('pnew') !== -1) {
            m.push({
                text: _('duplicate'),
                handler: this.duplicateContext,
                scope: this
            });
        }

        if (p.indexOf('pedit') !== -1) {
            m.push({
                text: _('edit'),
                handler: this.updateContext
            });
        }

        if (p.indexOf('premove') !== -1) {
            m.push('-');
            m.push({
                text: _('delete'),
                handler: this.remove,
                scope: this
            });
        }
        return m;
    },

    create: function(btn, e) {
        if (this.createWindow) {
            this.createWindow.destroy();
        }
        this.createWindow = MODx.load({
            xtype: 'modx-window-context-create',
            closeAction: 'close',
            listeners: {
                success: {
                    fn: function() {
                        this.afterAction();
                    },
                    scope: this
                }
            }
        });
        this.createWindow.show(e.target);
    },

    updateContext: function(itm, e) {
        MODx.loadPage('context/update', `key=${this.menu.record.key}`);
    },

    duplicateContext: function() {
        const
            record = {
                key: this.menu.record.key,
                newkey: ''
            },
            window = MODx.load({
                xtype: 'modx-window-context-duplicate',
                record: record,
                listeners: {
                    success: {
                        fn: function() {
                            this.refresh();
                            const tree = Ext.getCmp('modx-resource-tree');
                            if (tree) {
                                tree.refresh();
                            }
                        },
                        scope: this
                    }
                }
            });
        window.show();
    },

    remove: function(btn, e) {
        MODx.msg.confirm({
            title: _('warning'),
            text: _('context_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'Context/Remove',
                key: this.menu.record.key
            },
            listeners: {
                success: {
                    fn: function() {
                        this.afterAction();
                    },
                    scope: this
                }
            }
        });
    },

    afterAction: function() {
        const cmp = Ext.getCmp('modx-resource-tree');
        if (cmp) {
            cmp.refresh();
        }
        this.getSelectionModel().clearSelections(true);
        this.refresh();
    }
});
Ext.reg('modx-grid-contexts', MODx.grid.Context);

/**
 * Generates the create context window.
 *
 * @class MODx.window.CreateContext
 * @extends MODx.Window
 * @param {Object} config An object of options.
 * @xtype modx-window-context-create
 */
MODx.window.CreateContext = function(config = {}) {
    Ext.applyIf(config, {
        title: _('create'),
        url: MODx.config.connector_url,
        action: 'Context/Create',
        formDefaults: {
            anchor: '100%',
            validationEvent: 'change',
            validateOnBlur: false
        },
        fields: [{
            xtype: 'textfield',
            fieldLabel: _('context_key'),
            name: 'key',
            maxLength: 100,
            allowBlank: false
        }, {
            xtype: 'textfield',
            fieldLabel: _('name'),
            name: 'name',
            maxLength: 100,
            allowBlank: false
        }, {
            xtype: 'textarea',
            fieldLabel: _('description'),
            name: 'description',
            grow: true
        }, {
            xtype: 'numberfield',
            fieldLabel: _('rank'),
            name: 'rank'
        }],
        keys: []
    });
    MODx.window.CreateContext.superclass.constructor.call(this, config);
};
Ext.extend(MODx.window.CreateContext, MODx.Window);
Ext.reg('modx-window-context-create', MODx.window.CreateContext);
