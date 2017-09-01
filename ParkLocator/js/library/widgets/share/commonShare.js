/*global define,alert,console */
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
    "dojo/string",
    "dojo/Deferred",
    "esri/request",
    "dojo/i18n!application/js/library/nls/localizedStrings"
], function (declare, string, Deferred, esriRequest, sharedNls) {

    //========================================================================================================================//
    var _instance, CommonShare;

    CommonShare = declare("js.CommonShare", null, {
        sharedNls: sharedNls,
        /**
        * Create the tiny url
        * @param {string} urlStr is url to shrink
        * @param {string} tinyURLServiceURL is Bitly service
        * @memberOf widgets/share/commonShare
        */
        getTinyLink: function (urlStr, tinyURLServiceURL) {
            var encodedUri, shareUrl, deferred;

            deferred = new Deferred();
            // Attempt the shrinking of the URL
            try {
                encodedUri = encodeURIComponent(urlStr);
                shareUrl = string.substitute(tinyURLServiceURL, [encodedUri]);
                // send esri request to generate bitly url
                esriRequest({
                    url: shareUrl
                }, {
                    useProxy: true
                }).then(function (response) {
                    if (response.data && response.data.url) {
                        deferred.resolve(response.data.url);
                    } else {
                        deferred.resolve(urlStr);
                    }
                }, function (error) {
                    deferred.resolve(urlStr);
                    console.log(sharedNls.errorMessages.unableToShareURL);
                });
            } catch (ex) {
                deferred.resolve(urlStr);
            }
            return deferred;
        },


        /**
        * share application detail with selected share option
        * @param {Deferred} waitForUrl is deferred for shrinking url to share
        * @param {object} mapSharingOptions sharing site urls
        * @param {string} site Selected share option
        * return CommonShare instance
        * @memberOf widgets/share/commonShare
        */
        share: function (waitForUrl, mapSharingOptions, site) {
            var windowObj = (site === "facebook" || site === "twitter") ? window.open('', '_blank') : null;
            waitForUrl.then(function (urlToShare) {
                switch (site) {
                case "facebook":
                    windowObj.location.href = string.substitute(mapSharingOptions.FacebookShareURL, [urlToShare]);
                    break;
                case "twitter":
                    windowObj.location.href = string.substitute(mapSharingOptions.TwitterShareURL, [urlToShare]);
                    break;
                case "email":
                    parent.location.href = string.substitute(mapSharingOptions.ShareByMailLink, [urlToShare]);
                    break;
                }
            });
        }
    });
    // create singleton if it doesn't already exist
    if (!_instance) {
        _instance = new CommonShare();
    }
    return _instance;
});
