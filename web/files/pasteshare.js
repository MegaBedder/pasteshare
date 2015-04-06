/**
 * Javascript to control the pasteshare application interface
 *
 * @author Anthony Vitacco anthony@littlegno.me
 * @license MIT
 */
var pasteshare = function () {
    /** @property object The code mirror editor reference */
    var editor;
    
    /** @property bool Whether or not to use encryption for this paste */
    var encryptPaste = false;
    
    /** @property The initialization vector for the encryption */
    var iv;
    
    /** @property The key for the encryption */
    var key;
    
    /** @property The local information storage */
    var store;
    
    /**
     * This is the function that gets run when the page is first hit. It
     * basically wires the page up and provides the glue logic for everything.
     */
    this.init = function () {
        /**
         * Initialize the local storage
         */
        this.store = new StickyStore({});
        
        /**
         * Initialize the page and set
         */
        this.toggleEncryptionSection(0);
        $("#encryption").change(function () {
            pasteshare.setEncryption($("#encryption").prop("checked"));
        });
        
        /**
         * Initialize the code mirror editor
         */
        this.editor = CodeMirror.fromTextArea(document.getElementById("editor"), {
            lineNumbers: true,
            indentUnit: 4,
            smartIndent: true,
            indentWithTabs: false,
            theme: "mdn-like",
            scrollbarStyle: "overlay",
            styleActiveLine: true,
            matchBrackets: true,
        });
        
        /**
         * Set the editor high to be the page height
         */
        this.setEditorHeight();
        
        /**
         * Autoload new mode file and change mode when language is changed
         */
        $("#language").change(function (ev) {
            var language = $("#language").val();
            CodeMirror.modeURL = "/files/codemirror/mode/%N/%N.js";
            window.pasteshare.editor.setOption("mode", language);
            CodeMirror.autoLoadMode(window.pasteshare.editor, language);
        });
        
        /**
         * Bind the save paste function to the save button
         */
        $("#saveButton").click(function () {
            window.pasteshare.savePaste()
        });
        
        /**
         * Bind events for displaying an encrypted paste
         */
        if ($("#mode").val() == "view") {
            this.editor.setOption("readOnly", true);
            $("#language").change();
        }
        $("#encryptedModal").modal("show");
        $("#showDecryptModalButton").click(function () {
            $("#encryptedModal").modal("show");
        });
        $("#decryptButton").click(function () {
            window.pasteshare.decryptPaste()
        });
        
    }
    
    /**
     * The codemirror height is static which is annoying and ugly, this function
     * will set it to the hight of the window so it looks right
     */
    this.setEditorHeight = function () {
        /**
         * Set the height of the editor to the height of the window
         */
        $(".CodeMirror, .CodeMirror-scroll").css("height", $(window).height());
        this.editor.refresh();
    }
    
    /**
     * This function turns on and off the encryption
     *
     * @param boolean state The state of the encryption to set
     */
    this.setEncryption = function (state) {
        this.encryptPaste = state;
        this.toggleEncryptionSection();
    }
    
    /**
     * Show/Hide the encryption section depending on whether encryption is on or
     * off
     *
     * @param int duration Optional, defaults to 300 (ms)
     */
    this.toggleEncryptionSection = function (duration) {
        if (duration == null) {
            duration = 300;
        }
        if (this.encryptPaste) {
            this.startEncryption();
            $("#encryptionSection").show(duration);
            
        } else {
            $("#encryptionSection").hide(duration);
            this.stopEncryption();
        }
    }
    
    /**
     * Generate a 32-bit key and initialization vector and inject them into the
     * page
     */
    this.startEncryption = function () {
        this.key = forge.random.getBytesSync(32);
        this.iv = forge.random.getBytesSync(32);
        
        $("#key").val(encodeURI(this.key.toString()));
        $("#iv").val(encodeURI(this.iv.toString()));
    }
    
    /**
     * Unset the encryption key and initialization vector and remove them from
     * the page
     */
    this.stopEncryption = function () {
        this.key = null;
        this.iv = null;
        
        $("#key").val(null);
        $("#iv").val(null);
        
        this.encryptPaste = false;
    }
    
    /**
     * Post the paste contents to the back end and redirect to the correct page
     * after the result is returned.
     */
    this.savePaste = function () {
        /** Define the post hash, we'll need it */
        var post = {};
        
        /** Get the editor value from codemirror */
        var editorValue = this.editor.getValue();
        
        /** Encrypt the value if required */
        if (this.encryptPaste) {
            var cipher = forge.cipher.createCipher("AES-CBC", this.key);
            cipher.start({iv: this.iv});
            cipher.update(forge.util.createBuffer(editorValue));
            cipher.finish();
            
            var encrypted = cipher.output;
            editorValue = encrypted.toHex();
            post.encrypted = true;
            post.iv = encodeURI(this.iv.toString());
        }
        
        /** Build the rest of the post hash */
        post.contents = editorValue;
        post.language = $("#language").val();
        post.expires = $("#expiration").val();
        
        $.post("/save", post, function (data) {
            if (data.status == 403) {
                console.log("Redirect to an error page.");
            } else {
                window.location.replace(data.redirect);
            }
        });
    }
    
    /**
     * Attempt to decrypt the paste with the given key and set the editor
     * contents to the decrypted version.
     */
    this.decryptPaste = function () {
        /**
         * Recreate the Key and IV from stored values
         */
        var key = decodeURI($("#decryptionKey").val());
        var iv = decodeURI($("#decryptionIV").val());
        
        console.log("Key:" + key);
        console.log("IV:" + iv);
        
        /**
         * Read the encrypted text back as hex and decrypt it.
         */
        var decipher = forge.cipher.createDecipher("AES-CBC", key);
        decipher.start({iv: iv});
        var textToDecrypt = forge.util.createBuffer(
            forge.util.hexToBytes(
                this.editor.getValue()
            )
        );
        console.log(textToDecrypt);
        decipher.update(textToDecrypt);
        var success = decipher.finish();
        
        if (success) {
            $("#encryptedModal").modal("hide");
            this.editor.setValue(decipher.output.toString());
        }
    }
    
    /**
     *
     */
    //this.addRecentLanguage = function (language) {
    //    list = this.store.get("recentLanguages");
    //    
    //    // Avoid adding duplicate languages
    //    for (key in list) {
    //        if (list[key].mode == language) {
    //            return;
    //        }
    //    }
    //    
    //    if (!list) {
    //        list = [];
    //    }
    //    var langDetails = {"mode": language, "name": this.getLanguageName(language)};
    //    list.unshift(langDetails);
    //    
    //    if (list.length > 5) {
    //        list.pop();
    //    }
    //    
    //    this.store.set("recentLanguages", list);
    //}
    //
    ///**
    // *
    // */
    //this.getRecentLanguages = function () {
    //    return this.store.get("recentLanguages");
    //}
    //
    ///**
    // *
    // */
    //this.getLanguagesList = function () {
    //    recent = this.getRecentLanguages();
    //    data = {
    //        recent: recent,
    //        modes: CodeMirror.modeInfo
    //    };
    //    var fiveRecent = Mustache.render('<optgroup label="Recent">{{#recent}}<option value="{{ mode}}">{{ name }}</option>{{/recent}}</optgroup>', data);
    //    var allLanguages = Mustache.render('<optgroup label="All Languages">{{#modes}}<option value="{{ mode }}">{{ name }}</option>{{/modes}}</optgroup>', data);
    //    options = fiveRecent + allLanguages;
    //    $("#language").html(options);
    //}
    //
    ///**
    // *
    // */
    //this.getLanguageName = function (language) {
    //    languages = CodeMirror.modeInfo;
    //    for (key in languages) {
    //        if (languages[key].mode == language) {
    //            return languages[key].name;
    //        }
    //    }
    //    return false;
    //}
    
    /**
     * Run the init function for this object when it's loaded
     */
    $(document).ready(this.init.bind(this));
}

/**
 * Run the stinking thing!
 */
$(document).ready(function () {
    window.pasteshare = new pasteshare();
});
