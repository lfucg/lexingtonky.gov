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
    showApproxString: " @fr@ Approx", // Setting text for displaying approx route length of results in carousel pod's search results.
    buttons: {
        okButtonText: "@fr@ OK", // Command button in Splash Screen to enter into the main screen of the application.
        email: "@fr@ Email", // Shown when hovering the mouse pointer over ‘Email’ icon for sharing the current map extents via email; works with shareViaEmail tooltip.
        Facebook: "@fr@ Facebook", // Shown when hovering the mouse pointer over  ‘Facebook’ icon for sharing the current map extents via a Facebook post; works with shareViaFacebook tooltip.
        Twitter: "@fr@ Twitter", // Shown when hovering the mouse pointer over  ‘Twitter’ icon for sharing the current map extents via a Twitter tweet; works with shareViaTwitter tooltip.
        embedding: "@fr@ Embedding", // Shown when hovering the mouse pointer over  ‘Embedding’ icon for sharing the current map extents via a link.
        goButtonText: "@fr@ Go", // Command button in ‘Activity’ search to search the selected activities.
        backButtonText: "@fr@ Back", // Command button in ‘Info’ window ‘Comments’ pod to go back to the ‘Comments’ pod without submitting the comment..
        submitButtonText: "@fr@ Submit", // Command button in ‘Info’ window ‘Comments’ pod to submit the comment.
        postCommentButtonText: "@fr@ Post Comment" // Command button in ‘Info’ window ‘Comments’ pod to post comment.
    },
    tooltips: {
        searchTooltip: "@fr@ Search", // Shown as a tooltip for the search icon  in ‘Event’ search to search events based on the specified date.
        addPoint: "@fr@ Click to add point", // Shown as a tooltip for search by point icon.
        routeTooltip: "@fr@ Driving Directions", // Shown as a tooltip for ‘Driving directions’ icon for an individual feature in ‘My list’ and ‘Driving directions’ pod in ‘Info’ window pod.
        locateTooltip: "@fr@ Locate", // Shown as a tooltip for Geolocation icon in appHeader.
        shareTooltip: "@fr@ Share", // Shown as a tooltip for Share icon in appHeader to open the options available to share the application.
        helpTooltip: "@fr@ Help", // Shown as a tooltip for Help icon in appHeader to view the help file.
        eventsTooltip: "@fr@ My List", // Shown as a tooltip for My List icon in appHeader to view My List items.
        clearEntryTooltip: "@fr@ Clear", // Shown as a tooltip for Clear text, icon in unified search textbox to clear the text entered in the unified search text box.
        hidePanelTooltip: "@fr@ Hide panel", // Shown as a tooltip to Hide the carousel pod.
        showPanelTooltip: "@fr@ Show panel", // Shown as a tooltip to Show the carousel pod.
        printButtonTooltip: "@fr@ Print", // Shown as a tooltip for Print icon in Direction section of the carousel pod to print the direction.
        closeTooltip: "@fr@ Close", // Shown as a tooltip for Close icon in Info window pod to close the Info window pod.
        search: "@fr@ Search", // Shown as a tooltip for Search button icon next to Clear icon in unified search textbox to search the specified address.
        routeForListTooltip: "@fr@ Driving Directions - List Items", // Shown as a tooltip for Driving directions icon for all items in My list panel.
        addToCalendarForListTooltip: "@fr@ Add to Calendar - List Items", // Shown as a tooltip for Add to Calendar icon for all items in My list panel.
        printForListTooltip: "@fr@ Print - List Items", // Shown as a tooltip for Print icon for all items in My list to print all the items in My List panel.
        deleteFromListTooltip: "@fr@ Delete from List", // Shown as a tooltip for Delete icon of an individual feature in my list to delete the seleted item.
        addToCalanderTooltip: "@fr@ Add to Calendar", // Shown as a tooltip for Add to calendar icon of an individual feature in My list to add the selected item to calendar.
        galleryInfoTooltip: "@fr@ Gallery", // Shown as a tooltip for Gallery tab in infoWindow pod.
        informationTooltip: "@fr@ Information", // Shown as a tooltip for Information icon in infoWindow pod.
        commentInfoTooltip: "@fr@ Comments", // Shown as a tooltip for Comments icon in infoWindow pod.
        addToListTooltip: "@fr@ Add to My List", // Shown as a tooltip for Add to My list icon in carousel pod and infoWindow pod.
        previousFeatureTooltip: "@fr@ Previous Feature", // Shown as a tooltip for Previous feature icon in infoWindow pod.
        nextFeatureTooltip: "@fr@ Next Feature" // Shown as a tooltip for Next feature icon in infoWindow pod.
    },
    titles: {
        webpageDisplayText: "@fr@ Copy/Paste HTML into your web page", // Shown as a title when Embedding link share option is clicked/tapped.
        searchResultText: "@fr@ Search Result", // Shown as a title of the Search Result pod in carousel pod indicating the search result list.
        sliderDisplayText: "@fr@ Show results within ", // Shown as a label for buffer slider.
        clearSearch: "@fr@ Clear Search", // Shown as a title for clear search.
        directionText: "@fr@ Directions to", // Shown as a prefix in the title for Driving direction pod in carousel pod.
        galleryText: "@fr@ Gallery", // Shown as a title for Gallery pod in carousel pod.
        commentText: "@fr@ Comment", // Shown as a title for Comments pod in carousel pod.
        directionCurrentLocationText: "@fr@ My Location", // Setting title
        directionTextDistance: "@fr@ Distance:", // Shown as a label for the distance in Directions tab of infoWindow pod and carousel container pod indicating the route length.
        directionTextTime: "@fr@ Duration:", // Shown as a label for the route duration time in Directions tab of infoWindow pod and carousel container pod.
        activityListTabName: "@fr@ My List", // Shown as a title when My List tab is clicked/tapped.
        fromDateText: "@fr@ From Date", // Shown as a title in the Event Planner tab tab indicating the start date of the event.
        toDateText: "@fr@ To Date", // Shown as a title in the Event Planner tab tab indicating the end date of the event.
        rating: "@fr@ Rating", // Shown as a label displaying ratings of the feature in infoWindow comments pod.
        postCommentText: "@fr@ Enter Comment", // Shown as a placeholder in Comment pod's textbox in the infoWindow pod.
        backToMapText: "@fr@ Back to Map", // In the mobile view it is shown as an option in the Comments pod to go back to the map from infoWindow comment pod.
        orderByDate: "@fr@ Order by Date", // Shown as a title to sort the date in ascending/descending order in My List panel.
        numberOfFeaturesFoundNearAddress: "@fr@ Found ${0} facility(ies) near the address", // Shown as a title below ‘Search Results’ title in the carousel pod indicating the number of features found in buffer.
        numberOfFeaturesFound: "@fr@ Found ${0} facility(ies)", // Shown as a title below ‘Search Results’ title in the carousel pod when searching ‘Activities’ indicating the number of facilities found..
        numberOfEventsFound: "@fr@ Found ${0} Event", // Shown as a title below ‘Search Results’ title in the carousel pod when a event is selected in My List panel indicating the selected event..
        infoWindowTextURL: "@fr@ More info", // Shown as a link for URL label in infoWindow Information tab or carousel pod when searching Events.
        printWindowListTitleText: "@fr@ My List", // Shown as a label for the print window title
        minuteText: "@fr@ min", // Shown as min label for calculated direction in direction pod / tab
        hourText: "@fr@ hrs" // Shown as hrs label for calculated direction in direction pod / tab
    },
    errorMessages: {
        invalidSearch: "@fr@ No results found", // Shown when no results are found in event planner and unified search.
        falseConfigParams: "@fr@ Required configuration key values are either null or not exactly matching with layer attributes. This message may appear multiple times.", // Setting error message for configuration key.
        invalidLocation: "@fr@ Current location not found.", // Shown when geolocation is disabled.
        invalidProjection: "@fr@ Unable to plot current location on the map.", // Shown when map point is not definite.
        widgetNotLoaded: "@fr@ Unable to load widgets.", // Shown whenfacing issue on loading widgets.
        imageDoesNotFound: "@fr@ No photos available.", // Shown when no attachment is found in Gallery.
        facilityNotfound: "@fr@ No facilities found in buffer area.", // Shown when no facilities are found in the buffer area during unified search.
        noCommentsAvailable: "@fr@ No comments available.", // Shown when no comments found in Comments pod in infoWindow pod and in carousel pod.
        routeComment: "@fr@ Route could not be calculated from current location.", // Shown when distance is too long to create route.
        activityNotSelected: "@fr@ Please select activity to search.", // Shown when no activity is selected in activity search.
        activityPlannerInvalidToDate: "@fr@ Please select valid To Date", // Shown when the To date field in Event search is empty.
        activityPlannerInvalidFromDate: "@fr@ Please select valid From Date", // Shown when the From date field in Event search is empty.
        activityPlannerInvalidDates: "@fr@ Please select the valid date", // Shown when From date field and To date field in Event search are empty.
        commentString: "@fr@ Please enter comment", // Shown when Submit button in Comments pod of infoWindow pod is clicled/tapped without entering the comment.
        maxLengthCommentString: "@fr@ Comment should not exceed 250 characters .", // // Shown when Submit button in Comments pod of infoWindow pod is clicled/tapped and the comment string exceeds 250 words.
        commentError: "@fr@ Unable to add comments. Comments table is either absent or does not have write access.", // Shown when Submit button in Comments pod of infoWindow pod is clicled/tapped and the application isunable to add comments.
        addedActivities: "@fr@ All activities within specified date range are already added to My List.", // Shown when all the activities are added in My list from Event planner.
        activitySearchGeolocationText: "@fr@ Geolocation is not supported in selected browser.", // Shown when searching activity/event using IE8 browser.
        portalUrlNotFound: "@fr@ Portal URL cannot be empty", // Setting error message when portal url is empty.
        activityAlreadyAdded: "@fr@ This feature is already added to list", // Shown when a particular feature already exist in My list and is again attempted to be added to My List.
        errorInQueringLayer: "@fr@ Failed to query Comment layer", // Setting error message while querying comment layer.
        loadingText: "@fr@ Loading...", // Shown in the bottom left corner while loading legends in legend box.
        noLegend: "@fr@ No Legend Available.", // Shown when no legend is found the legend box.
        noBasemap: "@fr@ No Basemap Found", // Shown when no basemap found in webmap.
        fieldNotConfigured: "@fr@ Fields are not configured.", // Setting error message when fields of info popup is not configured.
        geolocationWidgetNotFoundMessage: "@fr@ Geolocation widget is not configured.", // Shown when geolocation is disabled and either activity is searched or create route from My list is selected.
        enablePodSettingsInConfig: "@fr@ Please enable the PodSettings in Config.", // Shown when activity layer is disabled in config.
        activityLayerNotconfigured: "@fr@ Activity layer is not configured", // Shown when activity layer is disabled in config.
        eventLayerNotconfigured: "@fr@ Event layer is not configured", // Shown when event layer is disabled in config.
        unableAddEventToCalendar: "@fr@ Data is too large.", // Shown error message when data is too large of an event while creating URL.
        unableToShareURL: "@fr@ Application could not be shared with current data", // Shown when some invalid data is present or some data is missing while share URL.
        unableToPerformQuery: "@fr@ Unable to perform query.", // Shown when query failed.
        unableAddEventToCalendarList: "@fr@ Too many events to 'Add to Calendar', either delete some events or add individually.", // Shown error message when data is too large for list of events while creating URL.
        improperFieldConfigured: "@fr@ Configured fields are improper." // show the value in comment pod when primary or foreign key field are configured improper.
    },
    //end of shared nls

    //App nls
    appErrorMessage: {
        webmapTitleError: "@fr@ Title and/or QueryLayerId parameters in SearchSettings do not match with configured webmap" // Shown when layer id and layer title do not match with the config search setting.
    }
    //End of App
});
