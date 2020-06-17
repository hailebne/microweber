<?php
$uid = uniqid();
$here = mw_includes_url() . 'toolbar/editor_tools/plupload/';
?>

<script>// mw.require('tools.js');</script>
<script>mw.require('url.js');</script>
<script>mw.require('events.js');</script>
<style type="text/css">
    html, body, #container, #pickfiles_<?php print $uid  ?> {
        position: absolute;
        width: 100%;
        height: 100% !important;
        top: 0;
        left: 0;
        background: transparent;
    }

    * {
        cursor: pointer;
    }

    .plupload.html5 {
        /* IE does not scales it correctly when visibility is set to hidden  */
        width: 100% !important;
        height: 100% !important;
    }
    .moxie-shim {
    	height:45px !important;
    }
</style>
<script type="text/javascript" src="<?php print $here ?>plupload.full.min.js"></script>
<?php /* <script type="text/javascript" src="<?php print $here ?>js/plupload.js"></script>
<script type="text/javascript" src="<?php print $here ?>js/plupload.html5.js"></script>
<script type="text/javascript" src="<?php print $here ?>js/plupload.html4.js"></script> */?>

<div id="container">
    <div id="pickfiles_<?php print $uid ?>">&nbsp;</div>
</div>
<script>
    mw.require('files.js');
</script>
<script>



    var uploader = function( options ) {
    //var upload = function( url, data, callback, type ) {
        options = options || {};
        var defaults = {
            multiple: false,
            progress: null,
            element: null,
            url: null,
            on: {},
            autostart: true,
            async: true,
        }
        var scope = this;
        this.settings = $.extend({}, defaults, options);

        this.create = function () {
            this.input = document.createElement('input')
            this.input.type = 'file';
            this.input.oninput = function () {
                scope.addFiles(this.files);
            }
        }

        this.files = [];
        this._uploading = false;
        this.uploading = function (state) {
            if(typeof state === 'undefined') {
                return this._uploading;
            }
            this._uploading = state;
        };

        this.validate = function (file) {
            return true;
        }

        this.addFile = function (file) {
            if(this.validate(file)) {
                if(!this.files.length || this.options.multiple){
                    this.files.push(file);
                    if(this.settings.on.fileAdded) {
                        this.settings.on.fileAdded(file)
                    }
                    $(scope).trigger('FileAdded', file);
                } else {
                    this.files = [file];
                    $(scope).trigger('FileAdded', file);
                    if(this.settings.on.fileAdded) {
                        this.settings.on.fileAdded(file)
                    }
                }
            }
        }

        this.addFiles = function (array) {
            if (array && array.length) {
                array.forEach(function (file) {
                    scope.addFile(file)
                });
                if(this.settings.on.filesAdded) {
                    this.settings.on.filesAdded(file)
                }
                $(scope).trigger('FilesAdded', file);
                if(this.settings.autostart) {
                    this.uploadFiles()
                }
            }
        }

        this.build = function () {
            if(this.settings.element) {
                this.$element = $(this.settings.element);
                this.element = this.$element[0];
                if(this.element) {
                    this.$element.empty().appear(this.input);
                }
            }
        }

        this.init = function() {
            this.create()
            this.build()
        }
        this.init();

        this.removeFile = function (file) {
            var i = this.files.indexOf(file);
            if (i > -1) {
                this.files.splice(i, 1);
            }
        };

        this.uploadFile = function (file, done) {
            var data = {
                name: file.name,
                chunk: 0,
                chunks: 1,
                file: file,
            }
            return this.upload(data, done)
        }
        this.uploadFiles = function () {
            if (this.settings.async) {
                if (this.files.length) {
                    this.uploading(true)
                    this.uploadFile(this.files[0], function () {
                        scope.uploadFiles()
                    })
                } else {
                    this.uploading(false)
                }
            } else {
                var count = 0;
                var all = this.files.length;
                this.uploading(true)
                this.files.forEach(function (file) {
                    scope.uploadFile(file, function () {
                        count++;
                        scope.uploading(false)
                        if(all === count) {
                            if(scope.settings.on.filesUploaded) {
                                scope.settings.on.filesUploaded()
                            }
                            $(scope).trigger('FilesUploaded');
                        }
                    })
                })
            }
        }
        this.upload = function (data, done) {
            if(!this.settings.url) {
                return;
            }
            return $.ajax({
                url: this.settings.url,
                type: 'post',
                processData: false,
                contentType: false,
                data: data,
                success: function (res) {
                    scope.removeFile(data.file);
                    if(done) {
                        done.call(res)
                    }
                    if(scope.settings.on.fileUploaded) {
                        scope.settings.on.fileUploaded(res)
                    }
                    $(scope).trigger('FileUploaded', res);
                },
                dataType: 'json',
                xhr: function () {
                    var xhr = new XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function (event) {
                        if (event.lengthComputable) {
                            var percent = (event.loaded / event.total) * 100;
                            if(scope.settings.on.progress) {
                                scope.settings.on.progress(percent, event)
                            }
                            $(scope).trigger('progress', [percent, event]);
                        }
                    });
                    return xhr;
                }
            });
        };

    };


</script>
<script>
    Name = this.name;
    mwd.body.className += ' ' + Name;
    Params = mw.url.getUrlParams(window.location.href);


    urlparams = '';
    if (!!Params.path) {
        urlparams += 'path=' + Params.path + '&';
    }
	 if (!!Params.autopath) {

        urlparams += 'autopath=' + Params.autopath + '&';
    }

    urlparams += 'token=<?php print mw_csrf_token($uid); ?>';

    $(document).ready(function () {
        $(mwd.body).mousedown(function (e) {
            e.preventDefault();
        });
        var multi = (Params.multiple == 'true');
        var filters = [
            {title: "", extensions: Params.filters || '*'}
        ];
         this_frame = parent.mw.$("iframe[name='" + Name + "']");
        uploader = new plupload.Uploader({
            runtimes: 'html5',
            browse_button: 'pickfiles_<?php print $uid  ?>',
            debug: 0,
            max_retries: 10,
            container: 'container',
          //  chunk_size: '1500kb',
            chunk_size: 1500000,
            url: '<?php print site_url('plupload'); ?>?' + urlparams,
            filters: filters,
            multi_selection: multi,
            drop_element: true
        });
        window.onmessage = function (event) {
            var data = JSON.parse(event.data);
            var base = mw.url.strip(uploader.settings.url);
            var params = mw.url.getUrlParams(uploader.settings.url);
            var u = base + "?" + json2url(params) + "&" + json2url(data);
            uploader.setOption('url', u);
        }
        uploader.init();
         uploader.bind('FilesAdded', function (up, files) {
            this_frame.trigger("FilesAdded", [files]);

            if (Params.autostart != 'false') {
                uploader.start();
                $(mwd.body).addClass("loading");
            }
        });
        uploader.bind('UploadProgress', function (up, file) {
            this_frame.trigger("progress", file);
        });
        uploader.bind('FileUploaded', function (up, file, info) {
            var json = jQuery.parseJSON(info.response);
            if (typeof json.error == 'undefined') {
                this_frame.trigger("FileUploaded", json);
            }
            else {
                this_frame.trigger("responseError", json);
                $(mwd.body).removeClass("loading");
            }
        });
        uploader.bind('UploadComplete', function (up, files) {
            this_frame.trigger("done", files);
            $(mwd.body).removeClass("loading");
        });
        uploader.bind('Error', function (up, err) {
            this_frame.trigger("error", err.file);
            $(mwd.body).removeClass("loading");
        });
        $(document.body).click(function () {
            this_frame.trigger("click");
        });
    });
</script>



