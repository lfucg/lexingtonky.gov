/*global define,dojo,appGlobals */
/*jslint browser:true,sloppy:true,nomen:true,unparam:true,plusplus:true,indent:4 */
/** @license
 | Copyright 2015 Esri
 |
 | Licensed under the Apache License, Version 2.0 (the "License");
 | you may not use this file except in compliance with the License.
 | You may obtain a copy of the License at
 |
 |    http://www.apache.org/licenses/LICENSE-2.0
 |
 | Unless required by applicable law or agreed to in writing, software
 | distributed under the License is distributed on an "AS IS" BASIS,
 | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 | See the License for the specific language governing permissions and
 | limitations under the License.
 */
//============================================================================================================================//
define([
    "dojo/_base/declare",
    "dojo/dom-construct",
    "dojo/dom-style",
    "dojo/_base/lang",
    "dojo/dom-attr",
    "dojo/on",
    "dojo/text!./templates/splashScreenTemplate.html",
    "dijit/_WidgetBase",
    "dijit/_TemplatedMixin",
    "dojo/dom-class",
    "dijit/_WidgetsInTemplateMixin",
    "dojo/i18n!application/js/library/nls/localizedStrings",
    "dijit/a11yclick"
], function (declare, domConstruct, domStyle, lang, domAttr, on, template, _WidgetBase, _TemplatedMixin, domClass, _WidgetsInTemplateMixin, sharedNls, a11yclick) {
    return declare([_WidgetBase, _TemplatedMixin, _WidgetsInTemplateMixin], {
        templateString: template,
        sharedNls: sharedNls,
        splashScreenScrollbar: null,

        /**
        * create share widget
        *
        * @class
        * @name widgets/splashScreen/splashScreen
        */
        postCreate: function () {
            this.inherited(arguments);
            domConstruct.create("div", { "class": "esriCTCustomButtonInner", "innerHTML": sharedNls.buttons.okButtonText }, this.customButton);
            this.own(on(this.customButton, a11yclick, lang.hitch(this, function () {
                this._hideSplashScreenDialog();
            })));
            this.domNode = domConstruct.create("div", { "class": "esriCTSplashScreen" }, document.body);
            this.domNode.appendChild(this.splashScreenScrollBarOuterContainer);
            domConstruct.create("div", { "class": "esriCTLoadingIndicator", "id": "splashscreenlodingIndicator" }, this.splashScreenScrollBarOuterContainer);
        },

        /**
        * Function to show splash screen dialog box on load
        * @memberOf widgets/splashScreen/splashScreen
        */
        showSplashScreenDialog: function () {
            var splashScreenContent;
            domStyle.set(this.domNode, "display", "block");
            splashScreenContent = domConstruct.create("div", { "class": "esriCTSplashContent" }, this.splashScreenScrollBarContainer);
            this.splashScreenScrollBarContainer.style.height = (this.splashScreenDialogContainer.offsetHeight - 70) + "px";
            domAttr.set(splashScreenContent, "innerHTML", appGlobals.configData.SplashScreen.SplashScreenContent);
        },

        /**
        * Function to hide splash screen dialog box
        * @memberOf widgets/splashScreen/splashScreen
        */
        _hideSplashScreenDialog: function () {
            domStyle.set(this.domNode, "display", "none");
        }
    });
});
