/*global define */
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
define({
    showApproxString: " @es@ Approx", // Setting text for displaying approx route length of results in carousel pod's search results.
    buttons: {
        okButtonText: "@es@ OK", // Command button in Splash Screen to enter into the main screen of the application.
        email: "@es@ Email", // Shown when hovering the mouse pointer over ‘Email’ icon for sharing the current map extents via email; works with shareViaEmail tooltip.
        Facebook: "@es@ Facebook", // Shown when hovering the mouse pointer over  ‘Facebook’ icon for sharing the current map extents via a Facebook post; works with shareViaFacebook tooltip.
        Twitter: "@es@ Twitter", // Shown when hovering the mouse pointer over  ‘Twitter’ icon for sharing the current map extents via a Twitter tweet; works with shareViaTwitter tooltip.
        embedding: "@es@ Embedding", // Shown when hovering the mouse pointer over  ‘Embedding’ icon for sharing the current map extents via a link.
        goButtonText: "@es@ Go", // Command button in ‘Activity’ search to search the selected activities.
        backButtonText: "@es@ Back", // Command button in ‘Info’ window ‘Comments’ pod to go back to the ‘Comments’ pod without submitting the comment..
        submitButtonText: "@es@ Submit", // Command button in ‘Info’ window ‘Comments’ pod to submit the comment.
        postCommentButtonText: "@es@ Post Comment" // Command button in ‘Info’ window ‘Comments’ pod to post comment.
    },
    tooltips: {
        searchTooltip: "@es@ Search", // Shown as a tooltip for the search icon  in ‘Event’ search to search events based on the specified date.
        addPoint: "@es@ Click to add point", // Shown as a tooltip for search by point icon.
        routeTooltip: "@es@ Driving Directions", // Shown as a tooltip for ‘Driving directions’ icon for an individual feature in ‘My list’ and ‘Driving directions’ pod in ‘Info’ window pod.
        locateTooltip: "@es@ Locate", // Shown as a tooltip for Geolocation icon in appHeader.
        shareTooltip: "@es@ Share", // Shown as a tooltip for Share icon in appHeader to open the options available to share the application.
        helpTooltip: "@es@ Help", // Shown as a tooltip for Help icon in appHeader to view the help file.
        eventsTooltip: "@es@ My List", // Shown as a tooltip for My List icon in appHeader to view My List items.
        clearEntryTooltip: "@es@ Clear", // Shown as a tooltip for Clear text, icon in unified search textbox to clear the text entered in the unified search text box.
        hidePanelTooltip: "@es@ Hide panel", // Shown as a tooltip to Hide the carousel pod.
        showPanelTooltip: "@es@ Show panel", // Shown as a tooltip to Show the carousel pod.
        printButtonTooltip: "@es@ Print", // Shown as a tooltip for Print icon in Direction section of the carousel pod to print the direction.
        closeTooltip: "@es@ Close", // Shown as a tooltip for Close icon in Info window pod to close the Info window pod.
        search: "@es@ Search", // Shown as a tooltip for Search button icon next to Clear icon in unified search textbox to search the specified address.
        routeForListTooltip: "@es@ Driving Directions - List Items", // Shown as a tooltip for Driving directions icon for all items in My list panel.
        addToCalendarForListTooltip: "@es@ Add to Calendar - List Items", // Shown as a tooltip for Add to Calendar icon for all items in My list panel.
        printForListTooltip: "@es@ Print - List Items", // Shown as a tooltip for Print icon for all items in My list to print all the items in My List panel.
        deleteFromListTooltip: "@es@ Delete from List", // Shown as a tooltip for Delete icon of an individual feature in my list to delete the seleted item.
        addToCalanderTooltip: "@es@ Add to Calendar", // Shown as a tooltip for Add to calendar icon of an individual feature in My list to add the selected item to calendar.
        galleryInfoTooltip: "@es@ Gallery", // Shown as a tooltip for Gallery tab in infoWindow pod.
        informationTooltip: "@es@ Information", // Shown as a tooltip for Information icon in infoWindow pod.
        commentInfoTooltip: "@es@ Comments", // Shown as a tooltip for Comments icon in infoWindow pod.
        addToListTooltip: "@es@ Add to My List", // Shown as a tooltip for Add to My list icon in carousel pod and infoWindow pod.
        previousFeatureTooltip: "@es@ Previous Feature", // Shown as a tooltip for Previous feature icon in infoWindow pod.
        nextFeatureTooltip: "@es@ Next Feature" // Shown as a tooltip for Next feature icon in infoWindow pod.
    },
    titles: {
        webpageDisplayText: "@es@ Copy/Paste HTML into your web page", // Shown as a title when Embedding link share option is clicked/tapped.
        searchResultText: "@es@ Search Result", // Shown as a title of the Search Result pod in carousel pod indicating the search result list.
        sliderDisplayText: "@es@ Show results within ", // Shown as a label for buffer slider.
        clearSearch: "@es@ Clear Search", // Shown as a title for clear search.
        directionText: "@es@ Directions to", // Shown as a prefix in the title for Driving direction pod in carousel pod.
        galleryText: "@es@ Gallery", // Shown as a title for Gallery pod in carousel pod.
        commentText: "@es@ Comment", // Shown as a title for Comments pod in carousel pod.
        directionCurrentLocationText: "@es@ My Location", // Setting title
        directionTextDistance: "@es@ Distance:", // Shown as a label for the distance in Directions tab of infoWindow pod and carousel container pod indicating the route length.
        directionTextTime: "@es@ Duration:", // Shown as a label for the route duration time in Directions tab of infoWindow pod and carousel container pod.
        activityListTabName: "@es@ My List", // Shown as a title when My List tab is clicked/tapped.
        fromDateText: "@es@ From Date", // Shown as a title in the Event Planner tab tab indicating the start date of the event.
        toDateText: "@es@ To Date", // Shown as a title in the Event Planner tab tab indicating the end date of the event.
        rating: "@es@ Rating", // Shown as a label displaying ratings of the feature in infoWindow comments pod.
        postCommentText: "@es@ Enter Comment", // Shown as a placeholder in Comment pod's textbox in the infoWindow pod.
        backToMapText: "@es@ Back to Map", // In the mobile view it is shown as an option in the Comments pod to go back to the map from infoWindow comment pod.
        orderByDate: "@es@ Order by Date", // Shown as a title to sort the date in ascending/descending order in My List panel.
        numberOfFeaturesFoundNearAddress: "@es@ Found ${0} facility(ies) near the address", // Shown as a title below ‘Search Results’ title in the carousel pod indicating the number of features found in buffer.
        numberOfFeaturesFound: "@es@ Found ${0} facility(ies)", // Shown as a title below ‘Search Results’ title in the carousel pod when searching ‘Activities’ indicating the number of facilities found..
        numberOfEventsFound: "@es@ Found ${0} Event", // Shown as a title below ‘Search Results’ title in the carousel pod when a event is selected in My List panel indicating the selected event..
        infoWindowTextURL: "@es@ More info", // Shown as a link for URL label in infoWindow Information tab or carousel pod when searching Events.
        printWindowListTitleText: "@es@ My List", // Shown as a label for the print window title
        minuteText: "@es@ min", // Shown as min label for calculated direction in direction pod / tab
        hourText: "@es@ hrs" // Shown as hrs label for calculated direction in direction pod / tab
    },
    errorMessages: {
        invalidSearch: "@es@ No results found", // Shown when no results are found in event planner and unified search.
        falseConfigParams: "@es@ Required configuration key values are either null or not exactly matching with layer attributes. This message may appear multiple times.", // Setting error message when -------------------------------------
        invalidLocation: "@es@ Current location not found.", // Shown when geolocation is disabled.
        invalidProjection: "@es@ Unable to plot current location on the map.", // Shown when map point is not definite.
        widgetNotLoaded: "@es@ Unable to load widgets.", // Shown whenfacing issue on loading widgets.
        imageDoesNotFound: "@es@ No photos available.", // Shown when no attachment is found in Gallery.
        facilityNotfound: "@es@ No facilities found in buffer area.", // Shown when no facilities are found in the buffer area during unified search.
        noCommentsAvailable: "@es@ No comments available.", // Shown when no comments found in Comments pod in infoWindow pod and in carousel pod.
        routeComment: "@es@ Route could not be calculated from current location.", // Shown when distance is too long to create route.
        activityNotSelected: "@es@ Please select activity to search.", // Shown when no activity is selected in activity search.
        activityPlannerInvalidToDate: "@es@ Please select valid To Date", // Shown when the To date field in Event search is empty.
        activityPlannerInvalidFromDate: "@es@ Please select valid From Date", // Shown when the From date field in Event search is empty.
        activityPlannerInvalidDates: "@es@ Please select the valid date", // Shown when From date field and To date field in Event search are empty.
        commentString: "@es@ Please enter comment", // Shown when Submit button in Comments pod of infoWindow pod is clicled/tapped without entering the comment.
        maxLengthCommentString: "@es@ Comment should not exceed 250 characters .", // // Shown when Submit button in Comments pod of infoWindow pod is clicled/tapped and the comment string exceeds 250 words.
        commentError: "@es@ Unable to add comments. Comments table is either absent or does not have write access.", // Shown when Submit button in Comments pod of infoWindow pod is clicled/tapped and the application isunable to add comments.
        addedActivities: "@es@ All activities within specified date range are already added to My List.", // Shown when all the activities are added in My list from Event planner.
        activitySearchGeolocationText: "@es@ Geolocation is not supported in selected browser.", // Shown when searching activity/event using IE8 browser.
        portalUrlNotFound: "@es@ Portal URL cannot be empty", // Setting error message when portal url is empty.
        activityAlreadyAdded: "@es@ This feature is already added to list", // Shown when a particular feature already exist in My list and is again attempted to be added to My List.
        errorInQueringLayer: "@es@ Failed to query Comment layer", // Setting error message while querying comment layer.
        loadingText: "@es@ Loading...", // Shown in the bottom left corner while loading legends in legend box.
        noLegend: "@es@ No Legend Available.", // Shown when no legend is found the legend box.
        noBasemap: "@es@ No Basemap Found", // Shown when no basemap found in webmap.
        fieldNotConfigured: "@es@ Fields are not configured.", // Shown when event layer is disabled in config.
        geolocationWidgetNotFoundMessage: "@es@ Geolocation widget is not configured.", // Shown when geolocation is disabled and either activity is searched or create route from My list is selected.
        enablePodSettingsInConfig: "@es@ Please enable the PodSettings in Config.", // Setting error message when all pod's status is disabled.
        activityLayerNotconfigured: "@es@ Activity layer is not configured", // Shown when activity layer is disabled in config.
        eventLayerNotconfigured: "@es@ Event layer is not configured", // Shown when event layer is disabled in config.
        unableAddEventToCalendar: "@es@ Data is too large.", // Shown error message when data is too large of an event while creating URL.
        unableToShareURL: "@es@ Application could not be shared with current data", // Shown when some invalid data is present or some data is missing while share URL.
        unableToPerformQuery: "@es@ Unable to perform query.", // Shown when query failed.
        unableAddEventToCalendarList: "@es@ Too many events to 'Add to Calendar', either delete some events or add individually.", // Shown error message when data is too large for list of events while creating URL.
        improperFieldConfigured: "@es@ Configured fields are improper." // show the value in comment pod when primary or foreign key field are configured improper.
    },
    //end of shared nls

    //App nls
    appErrorMessage: {
        webmapTitleError: "@es@ Title and/or QueryLayerId parameters in SearchSettings do not match with configured webmap" // Shown when layer id and layer title do not match with the config search setting.
    }
    //End of App
});
