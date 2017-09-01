/*global define,dojo,dojoConfig:true,alert,esri,console,Modernizr,dijit,appGlobals */
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
    "dojo/dom",
    "dojo/dom-construct",
    "dojo/dom-style",
    "dojo/_base/lang",
    "dojo/on",
    "dojo/dom-class",
    "dojo/query",
    "dojo/string",
    "dojo/window",
    "dijit/_WidgetBase",
    "dojo/i18n!application/js/library/nls/localizedStrings",
    "dojo/topic",
    "dijit/a11yclick",
    "dojo/NodeList-manipulate"

], function (declare, dom, domConstruct, domStyle, lang, on, domClass, query, string, win, _WidgetBase, sharedNls, topic, a11yclick) {
    //========================================================================================================================//

    return declare([_WidgetBase], {
        sharedNls: sharedNls,                                   // Variable for shared NLS
        /**
        * Display info window comment pod and posting data on feature
        *
        * @class
        * @name widgets/commonHelper/infoWindowCommentPod
        */
        /**
        * Comment Tab for InfoWindow
        * @param {object} commentObject contain attribute, index, featureLayer, widget name
        * @memberOf widgets/commonHelper/infoWindowCommentPod
        */
        _infoWindowCommentTab: function (commentObject) {
            var SearchSettingsLayersForComment, searchSettingsData, i, outerCommentContainer, queryObject, j, commentsTabContainer;
            // Check widgetName is "infoactivity"
            if (commentObject.widgetName.toLowerCase() === "infoactivity") {
                searchSettingsData = appGlobals.operationLayerSettings;
                // Check widgetName is "infoevent"
            } else if (commentObject.widgetName.toLowerCase() === "infoevent") {
                searchSettingsData = appGlobals.operationLayerSettings;
            } else {
                for (j = 0; j < appGlobals.operationLayerSettings.length; j++) {
                    searchSettingsData = appGlobals.operationLayerSettings;
                }
            }
            // Loop for the searchSettingsData to fetch the information
            for (i = 0; i < searchSettingsData.length; i++) {
                SearchSettingsLayersForComment = searchSettingsData[i];
                // Check if "SearchSettingsLayersForComment.CommentsSettings" contain comment
                if (SearchSettingsLayersForComment.activitySearchSettings && SearchSettingsLayersForComment.activitySearchSettings.CommentsSettings && SearchSettingsLayersForComment.activitySearchSettings.CommentsSettings.Enabled) {
                    // Check if comment container is there or not
                    commentsTabContainer = dom.byId("commentsTabContainer");
                    if (commentsTabContainer) {
                        domConstruct.empty(commentsTabContainer);
                    }
                    outerCommentContainer = domConstruct.create("div", { "class": "esriCTCommentInfoOuterContainer" }, commentsTabContainer);
                    domConstruct.create("div", { "class": "esriCTCommentContainer" }, outerCommentContainer);
                    queryObject = { "FeatureData": commentObject.attribute, "SolveRoute": null, "Index": commentObject.infoIndex, "QueryURL": searchSettingsData[0].QueryURL, "WidgetName": commentObject.widgetName, "Address": null, "IsRouteCreated": false, "primaryFieldType": commentObject.primaryFieldType };
                    topic.publish("showProgressIndicator");
                    this.queryCommentLayer(queryObject);
                }
            }
        },

        /**
        * Set infoWindow Comments
        * @param {object} result contain comments of feature
        * @param {field} featureId is ObhectID
        * @param {number} resultContent contains index number of feature
        * @memberOf widgets/commonHelper/infoWindowCommentPod
        */
        _setInfoWindowComment: function (result, featureId, queryObject, errorMessage) {
            var j, divHeaderStar, divStar, message, l, isCommentFound = false, divCommentRow, postCommentContainer, i, commentValue, divContentDiv,
                esriCTCommentDateStar, postCommentButton, infocontainer, divCommentContainer, divCommentRowCont, formatedDate;
            try {
                for (i = 0; i < appGlobals.operationLayerSettings.length; i++) {
                    if (queryObject.WidgetName.toLowerCase() === "infoactivity") {
                        if (appGlobals.operationLayerSettings[i].activitySearchSettings && appGlobals.operationLayerSettings[i].activitySearchSettings.CommentsSettings) {
                            this.searchSettingsData = appGlobals.operationLayerSettings[i].activitySearchSettings;
                            break;
                        }
                    }
                }
                divCommentContainer = query('.esriCTCommentContainer')[0];
                divContentDiv = domConstruct.create("div", { "class": "esriCTCommentInfoContent" }, divCommentContainer);
                this.removeNullValue(result);
                if (result && result.length > 0) {
                    // loop for result to fetch comments
                    for (l = 0; l < result.length; l++) {
                        commentValue = string.substitute(this.searchSettingsData.CommentsSettings.CommentField, result[l].attributes);
                        if (commentValue !== appGlobals.configData.ShowNullValueAs) {
                            divCommentRowCont = domConstruct.create("div", { "class": "esriCTDivCommentRowCont" }, divContentDiv);
                            divCommentRow = domConstruct.create("div", { "class": "esriCTDivCommentRow" }, divCommentRowCont);
                            esriCTCommentDateStar = domConstruct.create("div", { "class": "esriCTCommentDateStar" }, divCommentRow);
                            divHeaderStar = domConstruct.create("div", { "class": "esriCTHeaderRatingStar" }, esriCTCommentDateStar);
                            isCommentFound = true;
                            //loop for rating star
                            for (j = 0; j < 5; j++) {
                                divStar = domConstruct.create("span", { "class": "esriCTRatingStar" }, divHeaderStar);
                                if (j < string.substitute(this.searchSettingsData.CommentsSettings.RankField, result[l].attributes)) {
                                    domClass.add(divStar, "esriCTRatingStarChecked");
                                }
                            }
                            domConstruct.create("div", { "class": "esriCTCommentText", "innerHTML": this._getFormattedCommentText(commentValue) }, divCommentRow);
                            formatedDate = this._changeDateFormatForComment(result[l].attributes, this.searchSettingsData.CommentsSettings.SubmissionDateField);
                            domConstruct.create("div", { "class": "esriCTCommentDateInfoWindow", "innerHTML": formatedDate }, esriCTCommentDateStar);
                        }
                    }
                }
                // Check if comment not found
                if (!isCommentFound) {
                    message = "";
                    domConstruct.empty(divContentDiv);
                    if (errorMessage) {
                        message = errorMessage;
                    } else {
                        message = sharedNls.errorMessages.noCommentsAvailable;
                    }
                    domConstruct.create("div", { "class": "esriCTNullCommentText", "innerHTML": message }, divContentDiv);
                }
                domClass.add(divCommentContainer, "esriCTInfoContentComment");
                postCommentContainer = domConstruct.create("div", { "class": "esriCTButtonDiv" }, null);
                postCommentButton = domConstruct.create("div", { "class": "esriCTInfoPostButton", "innerHTML": sharedNls.buttons.postCommentButtonText }, postCommentContainer);
                infocontainer = query('.esriCTCommentContainer')[0];
                domConstruct.place(postCommentContainer, infocontainer, "after");
                // Click of postCommentButton on comment panel in infowindow
                this.own(on(postCommentButton, a11yclick, lang.hitch(this, function () {
                    this._postComment(featureId, result);
                })));
            } catch (error) {
                alert(error);
            }
        },

        /**
        * Set the content in infoWindow for post comment
        * @param {object} commentID is objectID from layer
        * @param {object} result contains Date,Comment and star
        * @memberOf widgets/commonHelper/infoWindowCommentPod
        */
        _postComment: function (commentID, result) {
            var divStarRating, postCommentContainer, divCTPostCommentContainer, buttonDiv, backButton, submitButton, j, starInfoWindow = [], backToMapHide, postCommentContent, outerCommentContainer, textAreaContainerdiv;
            backToMapHide = query('.esriCTCloseDivMobile')[0];
            outerCommentContainer = query('.esriCTCommentInfoOuterContainer')[0];
            // Check "backToMapHide" node
            if (backToMapHide) {
                domStyle.set(backToMapHide, "display", "none");
            }
            domStyle.set(outerCommentContainer, "display", "none");
            divCTPostCommentContainer = dom.byId("divCTPostCommentContainer");
            if (divCTPostCommentContainer) {
                domConstruct.destroy(divCTPostCommentContainer);
            }
            postCommentContainer = domConstruct.create("div", { "id": "divCTPostCommentContainer", "class": "esriCTCommentInfoOuterContainer" }, dom.byId("commentsTabContainer"));
            postCommentContent = domConstruct.create("div", { "class": "esriCTPostCommentContainer" }, postCommentContainer);
            domConstruct.create("div", { "class": "esriCTHeaderTextRating", "innerHTML": sharedNls.titles.rating }, postCommentContent);
            divStarRating = domConstruct.create("div", { "class": "esriCTStarPostComment" }, postCommentContent);
            // Loop through rating stars to set rating for each comment
            for (j = 0; j < 5; j++) {
                this.rankValue = 0;
                this._checked = false;
                starInfoWindow[j] = domConstruct.create("div", { "class": "esriCTRatingStarPostComment" }, divStarRating);
                this.own(on(starInfoWindow[j], a11yclick, lang.hitch(this, this._selectedStarForPostComment, starInfoWindow, j)));
                this.own(on(starInfoWindow[j], "mouseover", lang.hitch(this, this._selectHoverStars, starInfoWindow, j)));
                this.own(on(starInfoWindow[j], "mouseout", lang.hitch(this, this._deSelectHoverStars, starInfoWindow, j)));
            }
            textAreaContainerdiv = domConstruct.create("div", { "class": "textAreaContainerdiv" }, postCommentContainer);
            domConstruct.create("textarea", { "class": "esriCTTextAreaContainer", "id": "txtComments", "placeholder": sharedNls.titles.postCommentText }, textAreaContainerdiv);
            buttonDiv = domConstruct.create("div", { "class": "esriCTButtonDiv" }, postCommentContainer);
            backButton = domConstruct.create("div", { "class": "esriCTInfoBackButton", "innerHTML": sharedNls.buttons.backButtonText }, buttonDiv);
            submitButton = domConstruct.create("div", { "class": "esriCTInfoSubmitButton", "innerHTML": sharedNls.buttons.submitButtonText }, buttonDiv);
            // Click of backButton of comment tap in infowindow
            this.own(on(backButton, a11yclick, lang.hitch(this, function () {
                this._backButton();
            })));
            // Click of submit button of comment in infowindow
            this.own(on(submitButton, a11yclick, lang.hitch(this, function () {
                if (commentID) {
                    if (this.queryString !== "") {
                        this._submitButton(commentID, result);
                    } else {
                        alert(sharedNls.errorMessages.improperFieldConfigured);
                    }
                } else {
                    alert(sharedNls.errorMessages.fieldNotConfigured);
                }
            })));
        },

        /**
        * Submit button in infoWindow for post comment
        * @param {object} selectedFeatureID contains selected featureID
        * @param {object} result contains Date,Comment and star
        * @memberOf widgets/commonHelper/infoWindowCommentPod
        */
        _submitButton: function (selectedFeatureID, result) {
            var commentsLayer, commentSubmitField, txtComments, ForeignKeyField, divCTPostCommentContainer, commentGraphic, currenDate, divCommentRowCont, currentDateFormat, attr, self, setAttribute, updatedComments, i, k, currentMonth, divCommentRow, outerCommentContainer,
                destroyCommentText, esriCTCommentDateStar, divHeaderStar, divStar, backToMapHide, backText, contentDivAfterNewComment, divContentDiv, updatedComment, comment, formatedDate;
            self = this;
            txtComments = dom.byId("txtComments");
            outerCommentContainer = query('.esriCTCommentInfoOuterContainer')[0];
            divContentDiv = query('.esriCTCommentInfoContent')[0];
            topic.publish("showProgressIndicator");
            backText = query('.esriCTInfoBackButton')[0];
            backToMapHide = query('.esriCTCloseDivMobile')[0];
            // Check comment Text value
            if (lang.trim(txtComments.value) === "" && self.rankValue === 0) {
                domStyle.set(outerCommentContainer, "display", "none");
                alert(sharedNls.errorMessages.commentString);
                topic.publish("hideProgressIndicator");
            } else if (lang.trim(txtComments.value) === "") {
                txtComments.focus();
                alert(sharedNls.errorMessages.commentString);
                topic.publish("hideProgressIndicator");
            } else if (txtComments.value.length > 250) {
                txtComments.focus();
                alert(sharedNls.errorMessages.maxLengthCommentString);
                topic.publish("hideProgressIndicator");
            } else {
                // Loop for ActivitySearchSettings  which is from config file
                for (i = 0; i < appGlobals.configData.ActivitySearchSettings.length; i++) {
                    commentsLayer = new esri.layers.FeatureLayer(appGlobals.configData.ActivitySearchSettings[i].CommentsSettings.QueryURL, {
                        mode: esri.layers.FeatureLayer.MODE_SELECTION,
                        outFields: ["*"]
                    });
                    commentGraphic = new esri.Graphic();
                    currenDate = new Date();
                    currentMonth = currenDate.getMonth();
                    currentMonth = currentMonth + 1;
                    currentDateFormat = currenDate.getTime();
                    attr = {};
                    // Setting comment attributes for applying on feature
                    ForeignKeyField = this.getKeyValue(appGlobals.configData.ActivitySearchSettings[i].CommentsSettings.ForeignKeyFieldForComment);
                    attr[ForeignKeyField] = selectedFeatureID;
                    attr[this.getKeyValue(appGlobals.configData.ActivitySearchSettings[i].CommentsSettings.CommentField)] = lang.trim(txtComments.value);
                    attr[this.getKeyValue(appGlobals.configData.ActivitySearchSettings[i].CommentsSettings.SubmissionDateField)] = currentDateFormat;
                    attr[this.getKeyValue(appGlobals.configData.ActivitySearchSettings[i].CommentsSettings.RankField)] = this.rankValue;
                    comment = lang.trim(txtComments.value);
                    updatedComment = self._getFormattedCommentText(comment);
                    setAttribute = {
                        comments: updatedComment,
                        rank: self.rankValue
                    };
                    commentSubmitField = this.getKeyValue(appGlobals.configData.ActivitySearchSettings[0].CommentsSettings.SubmissionDateField);
                    setAttribute[commentSubmitField] = currentDateFormat;
                }
                commentGraphic.setAttributes(attr);
                updatedComments = [];
                commentsLayer.applyEdits([commentGraphic], null, null, lang.hitch(this, function (msg) {
                    if (!msg[0].error) {
                        // Check window width
                        if (win.getBox().w <= 766) {
                            domStyle.set(backText, "display", "none");
                            if (backToMapHide) {
                                domStyle.set(backToMapHide, "display", "block");
                            }
                        }
                        updatedComments.push({ "attributes": setAttribute });
                        // Loop for result which contains date, comments and rating star
                        for (i = 0; i < result.length; i++) {
                            updatedComments.push(result[i]);
                        }
                        divCTPostCommentContainer = dom.byId("divCTPostCommentContainer");
                        domStyle.set(divCTPostCommentContainer, "display", "none");
                        domStyle.set(outerCommentContainer, "display", "block");
                        destroyCommentText = query('.esriCTNullCommentText')[0];
                        // Verify if "destroyCommentText" node is present
                        if (destroyCommentText) {
                            domConstruct.destroy(destroyCommentText);
                        }
                        divCommentRowCont = domConstruct.create("div", { "class": "esriCTDivCommentRowCont" }, divContentDiv);
                        divCommentRow = domConstruct.create("div", { "class": "esriCTDivCommentRow" }, divCommentRowCont);
                        esriCTCommentDateStar = domConstruct.create("div", { "class": "esriCTCommentDateStar" }, divCommentRow);
                        divHeaderStar = domConstruct.create("div", { "class": "esriCTHeaderRatingStar" }, esriCTCommentDateStar);
                        // Loop for the rating star
                        for (k = 0; k < 5; k++) {
                            divStar = domConstruct.create("span", { "class": "esriCTRatingStar" }, divHeaderStar);
                            if (k < setAttribute.rank) {
                                domClass.add(divStar, "esriCTRatingStarChecked");
                            }
                        }
                        domConstruct.create("div", { "class": "esriCTCommentText", "innerHTML": setAttribute.comments }, divCommentRow);
                        formatedDate = this._changeDateFormatForComment(updatedComments[0].attributes, appGlobals.configData.ActivitySearchSettings[0].CommentsSettings.SubmissionDateField, currenDate);
                        domConstruct.create("div", { "class": "esriCTCommentDateInfoWindow", "innerHTML": formatedDate }, esriCTCommentDateStar);
                        contentDivAfterNewComment = query('.esriCTDivCommentRowCont')[0];
                        if (contentDivAfterNewComment && contentDivAfterNewComment.children.length > 0) {
                            contentDivAfterNewComment.insertBefore(divCommentRow, contentDivAfterNewComment.children[0]);
                        } else {
                            divContentDiv.appendChild(divCommentRow);
                        }
                        topic.publish("hideProgressIndicator");
                    } else {
                        topic.publish("hideProgressIndicator");
                        alert(msg[0].error.message);
                    }
                }), function (err) {
                    topic.publish("hideProgressIndicator");
                    alert(sharedNls.errorMessages.commentError);
                });
            }
        },

        /**
        * Change the date format with configured date format
        * @param {object} object of featureSet
        * @param {object} commentValue contains comment attribute
        * @param {object} postCommentValue contains the comment if comment is posted
        * @return {object} object of event Search Settings
        * @memberOf widgets/commonHelper/infoWindowCommentPod
        */
        _changeDateFormatForComment: function (attributes, commentValue, postCommentValue) {
            var i, l, j, layerDetails, fieldValue, fieldName, fieldInfo, DateValue, isDateFieldFound = false;
            for (l in attributes) {
                if (attributes.hasOwnProperty(l)) {
                    if ((!attributes[l]) && (attributes[l] !== 0 || lang.trim(String(attributes[l])) === "")) {
                        attributes[l] = appGlobals.configData.ShowNullValueAs;
                    }
                }
            }
            for (i = 0; i < appGlobals.operationLayerSettings.length; i++) {
                if (appGlobals.operationLayerSettings[i].layerDetails && appGlobals.operationLayerSettings[i].layerDetails.popupInfo) {
                    layerDetails = appGlobals.operationLayerSettings[i].layerDetails;
                    for (j = 0; j < layerDetails.popupInfo.fieldInfos.length; j++) {
                        try {
                            // Get field value from feature attributes
                            fieldValue = string.substitute(commentValue, attributes);
                        } catch (ex) {
                            fieldValue = appGlobals.configData.ShowNullValueAs;
                        }
                        fieldName = layerDetails.popupInfo.fieldInfos[j].fieldName;
                        fieldInfo = this.isDateField(fieldName, layerDetails.layerObject);
                        if (fieldInfo) {
                            if (fieldValue !== appGlobals.configData.ShowNullValueAs) {
                                isDateFieldFound = true;
                                if (postCommentValue) {
                                    fieldValue = postCommentValue.getTime();
                                }
                                fieldValue = this.setDateFormat(layerDetails.popupInfo.fieldInfos[j], fieldValue);
                                DateValue = fieldValue;
                            }
                        }
                    }
                }
            }
            if (!isDateFieldFound) {
                DateValue = this._setDateFormateWithoutPopUpConfiguration(fieldValue, commentValue, attributes, postCommentValue);
            }
            return DateValue;
        },

        /**
        * BackButton in infoWindow for post comment block
        * @memberOf widgets/commonHelper/infoWindowCommentPod
        */
        _backButton: function () {
            var backToMapHide, outerCommentContainer, divCTPostCommentContainer;
            divCTPostCommentContainer = dom.byId("divCTPostCommentContainer");
            backToMapHide = query('.esriCTCloseDivMobile')[0];
            outerCommentContainer = query('.esriCTCommentInfoOuterContainer')[0];
            domStyle.set(divCTPostCommentContainer, "display", "none");
            domStyle.set(outerCommentContainer, "display", "block");
            // Validate the width of window
            if (win.getBox().w <= 766) {
                domStyle.set(backToMapHide, "display", "block");
            }
        },

        /**
        * Set for date formate without popup configuration
        * @param {string} fieldValue contains field value
        * @param {object} commentValue contains comment attribute
        * @param {object} attributes contains comment attribute
        * @param {object} postCommentValue contains the comment if comment is posted
        * @return{string} dateValue contains changed date value
        * @memberOf widgets/commonHelper/infoWindowCommentPod
        */
        _setDateFormateWithoutPopUpConfiguration: function (fieldValue, commentValue, attributes, postCommentValue) {
            var dateObj, dateValue, formatedDate, isFormatedDate;
            isFormatedDate = false;
            fieldValue = string.substitute(commentValue, attributes);
            if (fieldValue) {
                formatedDate = Number(fieldValue);
                if (formatedDate) {
                    fieldValue = Number(fieldValue);
                } else {
                    isFormatedDate = true;
                }
                if (postCommentValue) {
                    fieldValue = postCommentValue.getTime();
                }
                if (!isFormatedDate) {
                    dateObj = new Date(fieldValue);
                    dateValue = dateObj.toLocaleDateString();
                } else {
                    dateValue = fieldValue;
                }
            }
            return dateValue;
        },

        /**
        * Selected star for postComment in infoWindow for post comment block
        * @param {object} result contains Date,Comment and star
        * @param {object} j contains the selected star for infoWindow in comment panel
        * @memberOf widgets/commonHelper/infoWindowCommentPod
        */
        _selectedStarForPostComment: function (result, j) {
            var i;
            // Check if a node result has class="esriCTRatingStarChecked" present
            if ((domClass.contains(result[j], "esriCTRatingStarChecked")) && this._checked) {
                // Loop through 0 to 4 as there are 5 rating stars.
                for (i = 4; i >= j; i--) {
                    domClass.replace(result[i], "esriCTRatingStar");
                }
                // Check if rating star is selected.
                if (j !== 0) {
                    domClass.add(result[j], "esriCTRatingStarChecked");
                    this.rankValue = j + 1;
                } else {
                    // When no rating is selected
                    this.rankValue = j;
                }
            } else {
                // Loop for the rating selected star
                for (i = 0; i <= j; i++) {
                    domClass.add(result[i], "esriCTRatingStarChecked");
                    this._checked = true;
                    this.rankValue = j + 1;
                }
            }
            this.checkedStars = query('.esriCTRatingStarChecked');
        },

        /**
        * Star selected on hover for postComment in infoWindow for post comment block
        * @param {object} result contains Date,Comment and star
        * @param {object} j contains the selected star for infoWindow in comment panel
        * @memberOf widgets/commonHelper/infoWindowCommentPod
        */
        _selectHoverStars: function (result, j) {
            var i;
            // Check if result is present
            if (result) {
                // Loop for selected star for infoWindow in comment panel
                for (i = 0; i <= j; i++) {
                    // Check if a node result has class="esriCTRatingStar" present or class="esriCTRatingStarPostComment"
                    if (domClass.contains(result[i], "esriCTRatingStar") || domClass.contains(result[i], "esriCTRatingStarPostComment")) {
                        domClass.add(result[i], "esriCTRatingStarHover");
                    }
                }
            }
        },

        /**
        * Star selected on hover for postComment in infoWindow for post comment block
        * @param {object} result contains Date,Comment and star
        * @param {object} j contains the selected star for infoWindow in comment panel
        * @memberOf widgets/commonHelper/infoWindowCommentPod
        */
        _deSelectHoverStars: function (result, j) {
            var i;
            // Check whether result contain data
            if (result) {
                // Loop for selected star for infoWindow in comment panel
                for (i = 0; i <= j; i++) {
                    // Check if a node result has class="esriCTRatingStarHover" present
                    if (domClass.contains(result[i], "esriCTRatingStarHover")) {
                        domClass.remove(result[i], "esriCTRatingStarHover");
                    }
                }
            }
        },

        /**
        * This function is called for submitting comment on click of submit button
        * @param{object} commentText contains the comments data
        * @return{data} value contains the formatted comments data
        * @memberOf widgets/commonHelper/infoWindowCommentPod
        */
        _getFormattedCommentText: function (commentText) {
            // Checking for comment text for checking multiline comments
            if (commentText) {
                commentText = commentText.replace(/(?:\r\n|\r|\n)/g, '<br />');
            }
            return commentText;
        }
    });
});
