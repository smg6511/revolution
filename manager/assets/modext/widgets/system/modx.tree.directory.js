/**
 * Generates the Directory Tree
 *
 * @class MODx.tree.Directory
 * @extends MODx.tree.Tree
 * @param {Object} config An object of options.
 * @xtype modx-tree-directory
 */
MODx.tree.Directory = function(config = {}) {
    config.id = config.id || Ext.id();
    Ext.applyIf(config, {
        rootVisible: true,
        rootName: 'Filesystem',
        rootId: '/',
        title: _('files'),
        ddAppendOnly: true,
        ddGroup: 'modx-treedrop-sources-dd',
        url: MODx.config.connector_url,
        hideSourceCombo: false,
        baseParams: {
            hideFiles: config.hideFiles || false,
            hideTooltips: config.hideTooltips || false,
            wctx: MODx.ctx || 'web',
            currentAction: MODx.request.a || 0,
            currentFile: MODx.request.file || '',
            source: config.source || 0
        },
        action: 'Browser/Directory/GetList',
        primaryKey: 'dir',
        useDefaultToolbar: true,
        autoExpandRoot: false,
        tbar: [{
            cls: 'x-btn-icon icon-folder',
            tooltip: { text: _('file_folder_create') },
            handler: this.createDirectory,
            scope: this,
            hidden: !MODx.perm.directory_create
        }, {
            cls: 'x-btn-icon icon-page_white',
            tooltip: { text: _('file_create') },
            handler: this.quickCreateFile,
            scope: this,
            hidden: !MODx.perm.file_create
        }, {
            cls: 'x-btn-icon icon-file_upload',
            tooltip: { text: _('upload_files') },
            handler: this.uploadFiles,
            scope: this,
            hidden: !MODx.perm.file_upload
        }, '->', {
            cls: 'x-btn-icon icon-file_manager',
            tooltip: { text: _('modx_browser') },
            handler: this.loadFileManager,
            scope: this,
            hidden: !(MODx.perm.file_manager && !MODx.browserOpen)
        }],
        tbarCfg: {
            id: `${config.id}-tbar`
        }
    });
    MODx.tree.Directory.superclass.constructor.call(this, config);
    this.addEvents({
        beforeUpload: true,
        afterUpload: true,
        afterQuickCreate: true,
        afterRename: true,
        afterRemove: true,
        fileBrowserSelect: true,
        changeSource: true,
        afterSort: true
    });
    this._init();
    this.on({
        render: function() {
            const el = Ext.get(this.config.id);
            el.createChild({ tag: 'div', id: `${this.config.id}_tb` });
            el.createChild({ tag: 'div', id: `${this.config.id}_filter` });
            this.addSourceToolbar();
        },
        afterrender: this.showRefresh,
        afterSort: this._handleAfterDrop,
        click: function(node, e) {
            node.select();
            this.cm.activeNode = node;
            if (this.uploader !== undefined) {
                this.uploader.setBaseParams({ path: node.id });
            }
        },
        show: function() {
            if (!this.config.hideSourceCombo) {
                try { this.sourceCombo.show(); } catch (e) {
                    // tbd
                }
            }
        }
    });

    this.uploader = new MODx.util.MultiUploadDialog.Upload({
        url: MODx.config.connector_url,
        base_params: {
            action: 'Browser/File/Upload',
            wctx: MODx.ctx || '',
            source: this.getSource()
        }
    });
    this.uploader.on('beforeupload', this.beforeUpload, this);
    this.uploader.on('uploadsuccess', this.uploadSuccess, this);
    this.uploader.on('uploaderror', this.uploadError, this);
    this.uploader.on('uploadfailed', this.uploadFailed, this);
};
Ext.extend(MODx.tree.Directory, MODx.tree.Tree, {

    windows: {},
    browser: null,

    /**
     * Build the contextual menu for the root node
     *
     * @param {Ext.data.Node} node
     *
     * @returns {Array}
     */
    getRootMenu: function(node) {
        const menu = [];
        if (MODx.perm.directory_create) {
            menu.push({
                text: _('file_folder_create'),
                handler: this.createDirectory,
                scope: this
            });
        }
        if (MODx.perm.file_create) {
            menu.push({
                text: _('file_create'),
                handler: this.createFile,
                scope: this
            });
        }
        if (MODx.perm.file_upload) {
            menu.push({
                text: _('upload_files'),
                handler: this.uploadFiles,
                scope: this
            });
        }
        if (node.ownerTree.el.hasClass('pupdate')) {
            // User is allowed to edit media sources
            menu.push([
                '-',
                {
                    text: _('edit'),
                    handler: function() {
                        MODx.loadPage('source/update', `id=${node.ownerTree.source}`);
                    }
                }
            ]);
        }
        return menu;
    },

    /**
     * Override to handle root nodes contextual menus
     *
     * @param node
     * @param e
     */
    _showContextMenu: function(node, e) {
        this.cm.activeNode = node;
        this.cm.removeAll();
        let menu = [];

        menu.push({
            text: `<b>${node.attributes.text}</b>`,
            handler: function() { return false; },
            header: true
        });
        menu.push('-');

        if (node.isRoot) {
            menu = menu.concat(this.getRootMenu(node));
        } else if (node.attributes.menu && node.attributes.menu.items) {
            menu = menu.concat(node.attributes.menu.items);
        }

        if (menu && menu.length > 0) {
            this.addContextMenuItem(menu);
            this.cm.showAt(e.xy);
        }
        e.preventDefault();
        e.stopEvent();
    },

    /**
     * Create a refresh button on the root node
     *
     * @see MODx.Tree.Tree#_onAppend
     */
    showRefresh: function() {
        const
            node = this.getRootNode(),
            inlineButtonsLang = this.getInlineButtonsLang(node),
            elId = `${node.ui.elNode.id}_tools`,
            el = document.createElement('div')
        ;
        el.id = elId;
        el.className = 'modx-tree-node-tool-ct';
        node.ui.elNode.appendChild(el);

        MODx.load({
            xtype: 'modx-button',
            text: '',
            scope: this,
            tooltip: new Ext.ToolTip({
                title: inlineButtonsLang.refresh,
                target: this
            }),
            node: node,
            handler: function(btn, evt) {
                evt.stopPropagation(evt);
                node.reload();
            },
            iconCls: 'icon-refresh',
            renderTo: elId,
            listeners: {
                mouseover: function(button, e) {
                    button.tooltip.onTargetOver(e);
                },
                mouseout: function(button, e) {
                    button.tooltip.onTargetOut(e);
                }
            }
        });
    },

    addSourceToolbar: function() {
        this.sourceCombo = new MODx.combo.MediaSource({
            value: this.config.source || MODx.config.default_media_source,
            listWidth: 236,
            listeners: {
                select: {
                    fn: this.changeSource,
                    scope: this
                },
                loaded: {
                    fn: function(combo) {
                        const
                            record = combo.store.getById(this.config.source),
                            rootNode = this.getRootNode()
                        ;
                        if (rootNode && record) {
                            rootNode.setText(record.data.name);
                        }
                    },
                    scope: this
                }
            }
        });
        this.searchBar = new Ext.Toolbar({
            renderTo: this.tbar,
            id: `${this.config.id}-sourcebar`,
            items: [this.sourceCombo]
        });
        this.on('resize', function() {
            this.sourceCombo.setWidth(this.getWidth() - 12);
        }, this);
        if (this.config.hideSourceCombo) {
            try { this.sourceCombo.hide(); } catch (e) {
                // tbd
            }
        }
    },

    changeSource: function(combo) {
        this.cm.activeNode = '';
        const
            sourceId = combo.getValue(),
            rootNode = this.getRootNode()
        ;
        if (rootNode) {
            rootNode.setText(combo.getRawValue());
        }
        this.config.baseParams.source = sourceId;
        this.fireEvent('changeSource', sourceId);
        this.refresh();
    },

    /**
     * Expand the root node if appropriate
     */
    _init: function() {
        const
            treeState = Ext.state.Manager.get(this.treestate_id),
            rootPath = this.root.getPath('text')
        ;
        if (rootPath === treeState) {
            // Nothing to do
            return;
        }
        this.root.expand();
    },

    _initExpand: function() {
        const treeState = Ext.state.Manager.get(this.treestate_id);
        if (!Ext.isEmpty(this.config.openTo)) {
            this.selectPath(`/${_('files')}/${this.config.openTo}`, 'text');
        } else {
            this.expandPath(treeState, 'text');
        }
    },

    _saveState: function(node) {
        if (!node.expanded && !node.isRoot) {
            // Node has been collapsed, grab its parent
            node = node.parentNode;
        }
        if (node.id === this.config.openTo) {
            node.select();
        }
        const path = node.getPath('text');
        Ext.state.Manager.set(this.treestate_id, path);
    },

    _handleAfterDrop: function(o, r) {
        const
            targetNode = o.event.target,
            { dropNode } = o.event
        ;
        if (o.event.point === 'append' && targetNode) {
            const ui = targetNode.getUI();
            ui.addClass('haschildren');
            ui.removeClass('icon-resource');
        }
        if ((MODx.request.a === 'resource/update') && dropNode.attributes.pk === MODx.request.id) {
            const
                parentFieldCmb = Ext.getCmp('modx-resource-parent'),
                parentFieldHidden = Ext.getCmp('modx-resource-parent-hidden')
            ;
            if (parentFieldCmb && parentFieldHidden) {
                parentFieldHidden.setValue(dropNode.parentNode.attributes.pk);
                parentFieldCmb.setValue(dropNode.parentNode.attributes.text.replace(/(<([^>]+)>)/ig, ''));
            }
        }
        targetNode.reload(true);
    },

    _handleDrag: function(dropEvent) {
        const
            from = dropEvent.dropNode.attributes.id,
            to = dropEvent.target.attributes.id,
            orgSource = typeof dropEvent.dropNode.attributes.sid === 'number'
                ? dropEvent.dropNode.attributes.sid
                : this.config.baseParams.source
        ;
        let
            destSource = typeof dropEvent.target.attributes.sid === 'number'
                ? dropEvent.target.attributes.sid
                : 0
        ;
        if (!destSource) {
            destSource = dropEvent.tree.source;
        }

        MODx.Ajax.request({
            url: this.config.url,
            params: {
                source: orgSource,
                from: from,
                destSource: destSource,
                to: to,
                action: this.config.sortAction || 'Browser/Directory/Sort',
                point: dropEvent.point
            },
            listeners: {
                success: {
                    fn: function(response) {
                        const el = dropEvent.dropNode.getUI().getTextEl();
                        if (el) {
                            Ext.get(el).frame();
                        }
                        this.fireEvent('afterSort', { event: dropEvent, result: response });
                    },
                    scope: this
                },
                failure: {
                    fn: function(response) {
                        MODx.form.Handler.errorJSON(response);
                        this.refresh();
                        if (response.message !== '') {
                            MODx.msg.alert(_('error'), response.message);
                        } else if (response.data && response.data[0]) {
                            MODx.msg.alert(response.data[0].id, response.data[0].msg);
                        }
                        return false;
                    },
                    scope: this
                }
            }
        });
    },

    getPath: function(node) {
        let path = node?.attributes?.path || '';

        // a little bit of security: strip leading / or .
        // full path security checking has to be implemented on server
        path = path.replace(/^[\/\.]*/, '');
        return `${path}/`;
    },

    editFile: function(itm, e) {
        MODx.loadPage('system/file/edit', `file=${this.cm.activeNode.attributes.id}&source=${this.config.source}`);
    },

    openFile: function(itm, e) {
        if (this.cm.activeNode.attributes.urlExternal) {
            window.open(this.cm.activeNode.attributes.urlExternal);
        }
    },

    quickUpdateFile: function(itm, e) {
        const node = this.cm.activeNode;
        MODx.Ajax.request({
            url: MODx.config.connector_url,
            params: {
                action: 'Browser/File/Get',
                file: node.attributes.id,
                wctx: MODx.ctx || '',
                source: this.getSource()
            },
            listeners: {
                success: {
                    fn: function(response) {
                        const record = {
                            file: node.attributes.id,
                            name: node.text,
                            path: node.attributes.pathRelative,
                            source: this.getSource(),
                            content: response.object.content
                        },
                              window = MODx.load({
                                  xtype: 'modx-window-file-quick-update',
                                  record: record,
                                  listeners: {
                                      hide: {
                                          fn: function() {
                                              this.destroy();
                                          }
                                      }
                                  }
                              });
                        window.show(e.target);
                    },
                    scope: this
                }
            }
        });
    },

    createFile: function(itm, e) {
        const active = this.cm.activeNode;
        let dir = '';

        if (active && active.attributes) {
            if (active.isRoot || active.attributes.type === 'dir') {
                dir = active.attributes.id;
            } else if (active.attributes.type === 'file') {
                const { path } = active.attributes;
                dir = path.substr(0, path.lastIndexOf('/') + 1);
            }
        }

        MODx.loadPage('system/file/create', `directory=${dir}&source=${this.getSource()}`);
    },

    quickCreateFile: function(itm, e) {
        const
            node = this.cm.activeNode,
            directory = (node) ? decodeURIComponent(node.attributes.id) : '/',
            record = {
                directory: directory,
                source: this.getSource()
            },
            window = MODx.load({
                xtype: 'modx-window-file-quick-create',
                record: record,
                listeners: {
                    success: {
                        fn: function() {
                            this.fireEvent('afterQuickCreate');
                            this.refreshActiveNode();
                        },
                        scope: this
                    },
                    hide: {
                        fn: function() {
                            this.destroy();
                        }
                    }
                }
            })
        ;
        window.show(e.target);
    },

    loadFileManager: function(btn, e) {
        let refresh = false;
        if (this.browser === null) {
            this.browser = MODx.load({
                xtype: 'modx-browser',
                hideFiles: MODx.config.modx_browser_tree_hide_files,
                rootId: '/', // prevent JS error because ui.node.elNode is undefined when this is
                wctx: MODx.ctx,
                source: this.config.baseParams.source,
                listeners: {
                    select: {
                        fn: function(data) {
                            this.fireEvent('fileBrowserSelect', data);
                        },
                        scope: this
                    }
                }
            });
        } else {
            refresh = true;
        }
        if (this.browser) {
            this.browser.setSource(this.config.baseParams.source);
            if (refresh) {
                this.browser.win.tree.refresh();
            }
            this.browser.show();
        }
    },

    renameDirectory: function(item, e) {
        const
            node = this.cm.activeNode,
            record = {
                old_name: node.text,
                name: node.text,
                path: node.attributes.pathRelative,
                source: this.getSource()
            },
            window = MODx.load({
                xtype: 'modx-window-directory-rename',
                record: record,
                listeners: {
                    success: {
                        fn: this.refreshParentNode,
                        scope: this
                    },
                    hide: {
                        fn: function() {
                            this.destroy();
                        }
                    }
                }
            })
        ;
        window.show(e.target);
    },

    renameFile: function(item, e) {
        const
            node = this.cm.activeNode,
            record = {
                old_name: node.text,
                name: node.text,
                path: node.attributes.pathRelative,
                source: this.getSource()
            },
            window = MODx.load({
                xtype: 'modx-window-file-rename',
                record: record,
                listeners: {
                    success: {
                        fn: function() {
                            this.fireEvent('afterRename');
                            this.refreshParentNode();
                        },
                        scope: this
                    },
                    hide: {
                        fn: function() {
                            this.destroy();
                        }
                    }
                }
            })
        ;
        window.show(e.target);
    },

    createDirectory: function(item, e) {
        const
            node = this.cm && this.cm.activeNode ? this.cm.activeNode : false,
            record = {
                parent: node && node.attributes.type === 'dir' ? node.attributes.pathRelative : '/',
                source: this.getSource()
            },
            window = MODx.load({
                xtype: 'modx-window-directory-create',
                record: record,
                listeners: {
                    success: {
                        fn: function() {
                            const parent = Ext.getCmp('folder-parent').getValue();
                            if (
                                (this.cm.activeNode && this.cm.activeNode.constructor.name === 'constructor')
                                || parent === ''
                                || parent === '/'
                            ) {
                                this.refresh();
                            } else {
                                this.refreshActiveNode();
                            }
                        },
                        scope: this
                    },
                    hide: {
                        fn: function() {
                            this.destroy();
                        }
                    }
                }
            })
        ;
        window.show(e ? e.target : Ext.getBody());
    },

    setVisibility: function(item, e) {
        const
            node = this.cm.activeNode,
            record = {
                path: node.attributes.path,
                visibility: node.attributes.visibility,
                source: this.getSource()
            },
            window = MODx.load({
                xtype: 'modx-window-set-visibility',
                record: record,
                listeners: {
                    success: {
                        fn: this.refreshParentNode,
                        scope: this
                    },
                    hide: {
                        fn: function() {
                            this.destroy();
                        }
                    }
                }
            })
        ;
        window.show(e.target);
    },

    removeDirectory: function(item, e) {
        const
            node = this.cm.activeNode,
            directory = node.attributes.text
        ;
        MODx.msg.confirm({
            text: _('file_folder_remove_confirm', {
                directory: directory
            }),
            url: MODx.config.connector_url,
            params: {
                action: 'Browser/Directory/Remove',
                dir: node.attributes.path,
                wctx: MODx.ctx || '',
                source: this.getSource()
            },
            listeners: {
                success: {
                    fn: this._afterRemove,
                    scope: this
                }
            }
        });
    },

    removeFile: function(item, e) {
        const
            node = this.cm.activeNode,
            fileName = node.attributes.text,
            filePath = node.attributes.pathRelative
        ;
        MODx.msg.confirm({
            text: _('file_remove_confirm', {
                file: fileName
            }),
            url: MODx.config.connector_url,
            params: {
                action: 'Browser/File/Remove',
                file: filePath,
                wctx: MODx.ctx || '',
                source: this.getSource()
            },
            listeners: {
                success: {
                    fn: this._afterRemove,
                    scope: this
                }
            }
        });
    },

    /**
     * Operation executed after a node has been removed
     */
    _afterRemove: function() {
        this.fireEvent('afterRemove');
        this.refreshParentNode();
        this.cm.activeNode = null;
    },

    unpackFile: function(item, e) {
        const node = this.cm.activeNode;
        MODx.msg.confirm({
            text: `${_('file_download_unzip')} ${node.attributes.id}`,
            url: MODx.config.connectors_url,
            params: {
                action: 'Browser/File/Unpack',
                file: node.attributes.id,
                wctx: MODx.ctx || '',
                source: this.getSource(),
                path: node.attributes.directory
            },
            listeners: {
                success: {
                    fn: this.refreshParentNode,
                    scope: this
                }
            }
        });
    },

    downloadFile: function(item, e) {
        const node = this.cm.activeNode;
        MODx.Ajax.request({
            url: MODx.config.connector_url,
            params: {
                action: 'Browser/File/Download',
                file: node.attributes.pathRelative,
                wctx: MODx.ctx || '',
                source: this.getSource()
            },
            listeners: {
                failure: {
                    fn: function(response) {
                        MODx.msg.alert(_('alert'), response.message);
                    },
                    scope: this
                },
                success: {
                    fn: function(response) {
                        if (!Ext.isEmpty(response.object.url)) {
                            // eslint-disable-next-line max-len
                            window.location.href = `${MODx.config.connector_url}?action=Browser/File/Download&download=1&file=${response.object.url}&HTTP_MODAUTH=${MODx.siteId}&source=${this.getSource()}&wctx=${MODx.ctx}`;
                        }
                    },
                    scope: this
                }
            }
        });
    },

    copyRelativePath: function(item, e) {
        const
            node = this.cm.activeNode,
            dummyRelativePathInput = document.createElement('input')
        ;
        document.body.appendChild(dummyRelativePathInput);
        dummyRelativePathInput.setAttribute('value', node.attributes.pathRelative);

        dummyRelativePathInput.select();
        document.execCommand('copy');

        document.body.removeChild(dummyRelativePathInput);
    },

    getSource: function() {
        return this.config.baseParams.source;
    },

    uploadFiles: function() {
        this.uploader.setBaseParams({ source: this.getSource() });
        this.uploader.browser = MODx.config.browserview;
        this.uploader.show();
    },

    uploadError: function(dlg, file, data, rec) {},

    uploadFailed: function(dlg, file, rec) {},

    uploadSuccess: function() {
        if (this.cm.activeNode) {
            const node = this.cm.activeNode;
            if (node.isLeaf()) {
                const pn = (node.isLeaf() ? node.parentNode : node);
                if (pn) {
                    pn.reload();
                } else if (node.id.match(/.*?\/$/)) {
                    this.refreshActiveNode();
                }
                this.fireEvent('afterUpload', node);
            } else {
                this.refreshActiveNode();
            }
        } else {
            this.refresh();
            this.fireEvent('afterUpload');
        }
    },

    beforeUpload: function() {
        let path = this.config.openTo || this.config.rootId || '/';
        if (this.cm.activeNode) {
            path = this.getPath(this.cm.activeNode);
            if (this.cm.activeNode.isLeaf()) {
                path = this.getPath(this.cm.activeNode.parentNode);
            }
        }
        this.uploader.setBaseParams({
            action: 'Browser/File/Upload',
            path: path,
            wctx: MODx.ctx || '',
            source: this.getSource()
        });
        this.fireEvent('beforeUpload', this.cm.activeNode);
    }

});
Ext.reg('modx-tree-directory', MODx.tree.Directory);

/**
 * Generates the Create Directory window
 *
 * @class MODx.window.CreateDirectory
 * @extends MODx.Window
 * @param {Object} config An object of configuration options.
 * @xtype modx-window-directory-create
 */
MODx.window.CreateDirectory = function(config = {}) {
    Ext.applyIf(config, {
        title: _('file_folder_create'),
        url: MODx.config.connector_url,
        action: 'Browser/Directory/Create',
        fields: [{
            xtype: 'hidden',
            name: 'wctx',
            value: MODx.ctx || ''
        }, {
            xtype: 'hidden',
            name: 'source'
        }, {
            fieldLabel: _('name'),
            name: 'name',
            xtype: 'textfield',
            anchor: '100%',
            allowBlank: false
        }, {
            fieldLabel: _('file_folder_parent'),
            id: 'folder-parent',
            name: 'parent',
            xtype: 'textfield',
            anchor: '100%'
        }, {
            xtype: 'label',
            forId: 'folder-parent',
            html: _('file_folder_parent_desc'),
            cls: 'desc-under'
        }]
    });
    MODx.window.CreateDirectory.superclass.constructor.call(this, config);
};
Ext.extend(MODx.window.CreateDirectory, MODx.Window);
Ext.reg('modx-window-directory-create', MODx.window.CreateDirectory);

/**
 * Generates the Set Visibility window
 *
 * @class MODx.window.SetVisibility
 * @extends MODx.Window
 * @param {Object} config An object of configuration options.
 * @xtype modx-window-visibility
 */
MODx.window.SetVisibility = function(config = {}) {
    Ext.applyIf(config, {
        title: _('file_folder_visibility'),
        url: MODx.config.connector_url,
        action: 'Browser/Visibility',
        fields: [{
            xtype: 'hidden',
            name: 'wctx',
            value: MODx.ctx || ''
        }, {
            xtype: 'hidden',
            name: 'source'
        }, {
            name: 'path',
            fieldLabel: _('file_folder_path'),
            xtype: 'statictextfield',
            anchor: '100%',
            submitValue: true
        }, {
            fieldLabel: _('file_folder_visibility_label'),
            name: 'visibility',
            xtype: 'modx-combo-visibility',
            anchor: '100%',
            allowBlank: false
        }, {
            hideLabel: true,
            xtype: 'displayfield',
            value: _('file_folder_visibility_desc'),
            anchor: '100%',
            allowBlank: false
        }]
    });
    MODx.window.SetVisibility.superclass.constructor.call(this, config);
};
Ext.extend(MODx.window.SetVisibility, MODx.Window);
Ext.reg('modx-window-set-visibility', MODx.window.SetVisibility);

/**
 * Generates the Rename Directory window
 *
 * @class MODx.window.RenameDirectory
 * @extends MODx.Window
 * @param {Object} config An object of configuration options.
 * @xtype modx-window-directory-rename
 */
MODx.window.RenameDirectory = function(config = {}) {
    Ext.applyIf(config, {
        title: _('rename'),
        url: MODx.config.connector_url,
        action: 'Browser/Directory/Rename',
        fields: [{
            xtype: 'hidden',
            name: 'wctx',
            value: MODx.ctx || ''
        }, {
            xtype: 'hidden',
            name: 'source'
        }, {
            fieldLabel: _('path'),
            name: 'path',
            xtype: 'statictextfield',
            submitValue: true,
            anchor: '100%'
        }, {
            fieldLabel: _('old_name'),
            name: 'old_name',
            xtype: 'statictextfield',
            anchor: '100%'
        }, {
            fieldLabel: _('new_name'),
            name: 'name',
            xtype: 'textfield',
            anchor: '100%',
            allowBlank: false
        }]
    });
    MODx.window.RenameDirectory.superclass.constructor.call(this, config);
};
Ext.extend(MODx.window.RenameDirectory, MODx.Window);
Ext.reg('modx-window-directory-rename', MODx.window.RenameDirectory);

/**
 * Generates the Rename File window
 *
 * @class MODx.window.RenameFile
 * @extends MODx.Window
 * @param {Object} config An object of configuration options.
 * @xtype modx-window-file-rename
 */
MODx.window.RenameFile = function(config = {}) {
    Ext.applyIf(config, {
        title: _('rename'),
        url: MODx.config.connector_url,
        action: 'Browser/File/Rename',
        fields: [{
            xtype: 'hidden',
            name: 'wctx',
            value: MODx.ctx || ''
        }, {
            xtype: 'hidden',
            name: 'source'
        }, {
            fieldLabel: _('path'),
            name: 'path',
            xtype: 'statictextfield',
            submitValue: true,
            anchor: '100%'
        }, {
            fieldLabel: _('old_name'),
            name: 'old_name',
            xtype: 'statictextfield',
            anchor: '100%'
        }, {
            fieldLabel: _('new_name'),
            name: 'name',
            xtype: 'textfield',
            anchor: '100%',
            allowBlank: false
        }, {
            name: 'dir',
            xtype: 'hidden'
        }]
    });
    MODx.window.RenameFile.superclass.constructor.call(this, config);
};
Ext.extend(MODx.window.RenameFile, MODx.Window);
Ext.reg('modx-window-file-rename', MODx.window.RenameFile);

/**
 * Generates the Quick Update File window
 *
 * @class MODx.window.QuickUpdateFile
 * @extends MODx.Window
 * @param {Object} config An object of configuration options.
 * @xtype modx-window-file-quick-update
 */
MODx.window.QuickUpdateFile = function(config = {}) {
    Ext.applyIf(config, {
        title: _('file_quick_update'),
        width: 600,
        layout: 'anchor',
        url: MODx.config.connector_url,
        action: 'Browser/File/Update',
        fields: [{
            xtype: 'hidden',
            name: 'wctx',
            value: MODx.ctx || ''
        }, {
            xtype: 'hidden',
            name: 'source'
        }, {
            xtype: 'hidden',
            name: 'file'
        }, {
            fieldLabel: _('name'),
            name: 'name',
            xtype: 'statictextfield',
            anchor: '100%'
        }, {
            fieldLabel: _('path'),
            name: 'path',
            xtype: 'statictextfield',
            anchor: '100%'
        }, {
            fieldLabel: _('content'),
            xtype: 'textarea',
            name: 'content',
            anchor: '100%',
            height: 200
        }],
        keys: [{
            key: Ext.EventObject.ENTER,
            shift: true,
            fn: this.submit,
            scope: this
        }],
        buttons: [{
            text: config.cancelBtnText || _('cancel'),
            scope: this,
            handler: function() { this.hide(); }
        }, {
            text: config.saveBtnText || _('save'),
            scope: this,
            handler: function() { this.submit(false); }
        }, {
            text: config.saveBtnText || _('save_and_close'),
            cls: 'primary-button',
            scope: this,
            handler: this.submit
        }]
    });
    MODx.window.QuickUpdateFile.superclass.constructor.call(this, config);
};
Ext.extend(MODx.window.QuickUpdateFile, MODx.Window);
Ext.reg('modx-window-file-quick-update', MODx.window.QuickUpdateFile);

/**
 * Generates the Quick Create File window
 *
 * @class MODx.window.QuickCreateFile
 * @extends MODx.Window
 * @param {Object} config An object of configuration options.
 * @xtype modx-window-file-quick-create
 */
MODx.window.QuickCreateFile = function(config = {}) {
    Ext.applyIf(config, {
        title: _('file_quick_create'),
        width: 600,
        layout: 'anchor',
        url: MODx.config.connector_url,
        action: 'Browser/File/Create',
        fields: [{
            xtype: 'hidden',
            name: 'wctx',
            value: MODx.ctx || ''
        }, {
            xtype: 'hidden',
            name: 'source'
        }, {
            fieldLabel: _('directory'),
            name: 'directory',
            submitValue: true,
            xtype: 'statictextfield',
            anchor: '100%'
        }, {
            xtype: 'label',
            html: _('file_folder_parent_desc'),
            cls: 'desc-under'
        }, {
            fieldLabel: _('name'),
            name: 'name',
            xtype: 'textfield',
            anchor: '100%',
            allowBlank: false
        }, {
            fieldLabel: _('content'),
            xtype: 'textarea',
            name: 'content',
            anchor: '100%',
            height: 200
        }],
        keys: [{
            key: Ext.EventObject.ENTER,
            shift: true,
            fn: this.submit,
            scope: this
        }]
    });
    MODx.window.QuickCreateFile.superclass.constructor.call(this, config);
};
Ext.extend(MODx.window.QuickCreateFile, MODx.Window);
Ext.reg('modx-window-file-quick-create', MODx.window.QuickCreateFile);
