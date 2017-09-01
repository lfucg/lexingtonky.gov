/*global dojo,define,dojoConfig */
/*jslint browser:true,sloppy:true,nomen:true,unparam:true,plusplus:true,indent:4 */
/*
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
    "dijit/_WidgetBase",
    "dojo/text!./templates/printForEvent.html"

], function (declare, _WidgetBase, printEvent) {

    //========================================================================================================================//

    return declare([_WidgetBase], {
        /**
        * create print for list widget
        *
        * @class
        * @name widgets/printForEventWindow/printForEventWindow
        */
        postCreate: function () {
            this._showModal();
        },

        /**
        * Display print window
        * @name widgets/printForEventWindow/printForEventWindow
        */
        _showModal: function () {
            var dataObject, _self = this, printWindow;
            dataObject = {
                "eventData": _self.eventListData
            };
            // Opening window
            printWindow = window.open('', '_blank');
            printWindow.location.href = dojoConfig.baseURL + "/js/library/widgets/printForEvent/templates/printForEvent.html";
            printWindow.opener.mapData = dataObject;
        }
    });
});
