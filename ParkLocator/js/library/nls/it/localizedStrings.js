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
    showApproxString: " @it@ Approx", // Setting text for displaying approx route length of results in carousel pod's search results.
    buttons: {
        okButtonText: "@it@ OK", // Command button in Splash Screen to enter into the main screen of the application.
        email: "@it@ Email", // Shown when hovering the mouse pointer over ‘Email’ icon for sharing the current map extents via email; works with shareViaEmail tooltip.
        Facebook: "@it@ Facebook", // Shown when hovering the mouse pointer over  ‘Facebook’ icon for sharing the current map extents via a Facebook post; works with shareViaFacebook tooltip.
        Twitter: "@it@ Twitter", // Shown when hovering the mouse pointer over  ‘Twitter’ icon for sharing the current map extents via a Twitter tweet; works with shareViaTwitter tooltip.
        embedding: "@it@ Embedding", // Shown when hovering the mouse pointer over  ‘Embedding’ icon for sharing the current map extents via a link.
        goButtonText: "@it@ Go", // Command button in ‘Activity’ search to search the selected activities.
        backButtonText: "@it@ Back", // Command button in ‘Info’ window ‘Comments’ pod to go back to the ‘Comments’ pod without submitting the comment..
        submitButtonText: "@it@ Submit", // Command button in ‘Info’ window ‘Comments’ pod to submit the comment.
        postCommentButtonText: "@it@ Post Comment" // Command button in ‘Info’ window ‘Comments’ pod to post comment.
    },
    tooltips: {
        searchTooltip: "@it@ Search", // Shown as a tooltip for the search icon  in ‘Event’ search to search events based on the specified date.
        addPoint: "@it@ Click to add point", // Shown as a tooltip for search by point icon.
        routeTooltip: "@it@ Driving Directions", // Shown as a tooltip for ‘Driving directions’ icon for an individual feature in ‘My list’ and ‘Driving directions’ pod in ‘Info’ window pod.
        locateTooltip: "@it@ Locate", // Shown as a tooltip for Geolocation icon in appHeader.
        shareTooltip: "@it@ Share", // Shown as a tooltip for Share icon in appHeader to open the options available to share the application.
        helpTooltip: "@it@ Help", // Shown as a tooltip for Help icon in appHeader to view the help file.
        eventsTooltip: "@it@ My List", // Shown as a tooltip for My List icon in appHeader to view My List items.
        clearEntryTooltip: "@it@ Clear", // Shown as a tooltip for Clear text, icon in unified search textbox to clear the text entered in the unified search text box.
        hidePanelTooltip: "@it@ Hide panel", // Shown as a tooltip to Hide the carousel pod.
        showPanelTooltip: "@it@ Show panel", // Shown as a tooltip to Show the carousel pod.
        printButtonTooltip: "@it@ Print", // Shown as a tooltip for Print icon in Direction section of the carousel pod to print the direction.
        closeTooltip: "@it@ Close", // Shown as a tooltip for Close icon in Info window pod to close the Info window pod.
        search: "@it@ Search", // Shown as a tooltip for Search button icon next to Clear icon in unified search textbox to search the specified address.
        routeForListTooltip: "@it@ Driving Directions - List Items", // Shown as a tooltip for Driving directions icon for all items in My list panel.
        addToCalendarForListTooltip: "@it@ Add to Calendar - List Items", // Shown as a tooltip for Add to Calendar icon for all items in My list panel.
        printForListTooltip: "@it@ Print - List Items", // Shown as a tooltip for Print icon for all items in My list to print all the items in My List panel.
        deleteFromListTooltip: "@it@ Delete from List", // Shown as a tooltip for Delete icon of an individual feature in my list to delete the seleted item.
        addToCalanderTooltip: "@it@ Add to Calendar", // Shown as a tooltip for Add to calendar icon of an individual feature in My list to add the selected item to calendar.
        galleryInfoTooltip: "@it@ Gallery", // Shown as a tooltip for Gallery tab in infoWindow pod.
        informationTooltip: "@it@ Information", // Shown as a tooltip for Information icon in infoWindow pod.
        commentInfoTooltip: "@it@ Comments", // Shown as a tooltip for Comments icon in infoWindow pod.
        addToListTooltip: "@it@ Add to My List", // Shown as a tooltip for Add to My list icon in carousel pod and infoWindow pod.
        previousFeatureTooltip: "@it@ Previous Feature", // Shown as a tooltip for Previous feature icon in infoWindow pod.
        nextFeatureTooltip: "@it@ Next Feature" // Shown as a tooltip for Next feature icon in infoWindow pod.
    },
    titles: {
        webpageDisplayText: "@it@ Copy/Paste HTML into your web page", // Shown as a title when Embedding link share option is clicked/tapped.
        searchResultText: "@it@ Search Result", // Shown as a title of the Search Result pod in carousel pod indicating the search result list.
        sliderDisplayText: "@it@ Show results within ", // Shown as a label for buffer slider.
        clearSearch: "@it@ Clear Search", // Shown as a title for clear search.
        directionText: "@it@ Directions to", // Shown as a prefix in the title for Driving direction pod in carousel pod.
        galleryText: "@it@ Gallery", // Shown as a title for Gallery pod in carousel pod.
        commentText: "@it@ Comment", // Shown as a title for Comments pod in carousel pod.
        directionCurrentLocationText: "@it@ My Location", // Setting title for
        directionTextDistance: "@it@ Distance:", // Shown as a label for the distance in Directions tab of infoWindow pod and carousel container pod indicating the route length.
        directionTextTime: "@it@ Duration:", // Shown as a label for the route duration time in Directions tab of infoWindow pod and carousel container pod.
        activityListTabName: "@it@ My List", // Shown as a title when My List tab is clicked/tapped.
        fromDateText: "@it@ From Date", // Shown as a title in the Event Planner tab tab indicating the start date of the event.
        toDateText: "@it@ To Date", // Shown as a title in the Event Planner tab tab indicating the end date of the event.
        rating: "@it@ Rating", // Shown as a label displaying ratings of the feature in infoWindow comments pod.
        postCommentText: "@it@ Enter Comment", // Shown as a placeholder in Comment pod's textbox in the infoWindow pod.
        backToMapText: "@it@ Back to Map", // In the mobile view it is shown as an option in the Comments pod to go back to the map from infoWindow comment pod.
        orderByDate: "@it@ Order by Date", // Shown as a title to sort the date in ascending/descending order in My List panel.
        numberOfFeaturesFoundNearAddress: "@it@ Found ${0} facility(ies) near the address", // Shown as a title below ‘Search Results’ title in the carousel pod indicating the number of features found in buffer.
        numberOfFeaturesFound: "@it@ Found ${0} facility(ies)", // Shown as a title below ‘Search Results’ title in the carousel pod when searching ‘Activities’ indicating the number of facilities found..
        numberOfEventsFound: "@it@ Found ${0} Event", // Shown as a title below ‘Search Results’ title in the carousel pod when a event is selected in My List panel indicating the selected event..
        infoWindowTextURL: "@it@ More info", // Shown as a link for URL label in infoWindow Information tab or carousel pod when searching Events.
        printWindowListTitleText: "@it@ My List", // Shown as a label for the print window title
        minuteText: "@it@ min", // Shown as min label for calculated direction in direction pod / tab
        hourText: "@it@ hrs" // Shown as hrs label for calculated direction in direction pod / tab
    },
    errorMessages: {
        invalidSearch: "@it@ No results found", // Shown when no results are found in event planner and unified search.
        falseConfigParams: "@it@ Required configuration key values are either null or not exactly matching with layer attributes. This message may appear multiple times.", // Setting error message for configuration key.
        invalidLocation: "@it@ Current location not found.", // Shown when geolocation is disabled.
        invalidProjection: "@it@ Unable to plot current location on the map.", // Shown when map point is not definite.
        widgetNotLoaded: "@it@ Unable to load widgets.", // Shown whenfacing issue on loading widgets.
        imageDoesNotFound: "@it@ No photos available.", // Shown when no attachment is found in Gallery.
        facilityNotfound: "@it@ No facilities found in buffer area.", // Shown when no facilities are found in the buffer area during unified search.
        noCommentsAvailable: "@it@ No comments available.", // Shown when no comments found in Comments pod in infoWindow pod and in carousel pod.
        routeComment: "@it@ Route could not be calculated from current location.", // Shown when distance is too long to create route.
        activityNotSelected: "@it@ Please select activity to search.", // Shown when no activity is selected in activity search.
        activityPlannerInvalidToDate: "@it@ Please select valid To Date", // Shown when the To date field in Event search is empty.
        activityPlannerInvalidFromDate: "@it@ Please select valid From Date", // Shown when the From date field in Event search is empty.
        activityPlannerInvalidDates: "@it@ Please select the valid date", // Shown when From date field and To date field in Event search are empty.
        commentString: "@it@ Please enter comment", // Shown when Submit button in Comments pod of infoWindow pod is clicled/tapped without entering the comment.
        maxLengthCommentString: "@it@ Comment should not exceed 250 characters .", // // Shown when Submit button in Comments pod of infoWindow pod is clicled/tapped and the comment string exceeds 250 words.
        commentError: "@it@ Unable to add comments. Comments table is either absent or does not have write access.", // Shown when Submit button in Comments pod of infoWindow pod is clicled/tapped and the application isunable to add comments.
        addedActivities: "@it@ All activities within specified date range are already added to My List.", // Shown when all the activities are added in My list from Event planner.
        activitySearchGeolocationText: "@it@ Geolocation is not supported in selected browser.", // Shown when searching activity/event using IE8 browser.
        portalUrlNotFound: "@it@ Portal URL cannot be empty", // Setting error message when portal url is empty.
        activityAlreadyAdded: "@it@ This feature is already added to list", // Shown when a particular feature already exist in My list and is again attempted to be added to My List.
        errorInQueringLayer: "@it@ Failed to query Comment layer", // Setting error message while querying comment layer.
        loadingText: "@it@ Loading...", // Shown in the bottom left corner while loading legends in legend box.
        noLegend: "@it@ No Legend Available.", // Shown when no legend is found the legend box.
        noBasemap: "@it@ No Basemap Found", // Shown when no basemap found in webmap.
        fieldNotConfigured: "@it@ Fields are not configured.", // Setting error message when fields of info popup is not configured.
        geolocationWidgetNotFoundMessage: "@it@ Geolocation widget is not configured.", // Shown when geolocation is disabled and either activity is searched or create route from My list is selected.
        enablePodSettingsInConfig: "@it@ Please enable the PodSettings in Config.", // Setting error message when all pod's status is disabled.
        activityLayerNotconfigured: "@it@ Activity layer is not configured", // Shown when activity layer is disabled in config.
        eventLayerNotconfigured: "@it@ Event layer is not configured", // Shown when event layer is disabled in config.
        unableAddEventToCalendar: "@it@ Data is too large.", // Shown error message when data is too large of an event while creating URL.
        unableToShareURL: "@it@ Application could not be shared with current data", // Shown when some invalid data is present or some data is missing while share URL.
        unableToPerformQuery: "@it@ Unable to perform query.", // Shown when query failed.
        unableAddEventToCalendarList: "@it@ Too many events to 'Add to Calendar', either delete some events or add individually.", // Shown error message when data is too large for list of events while creating URL.
        improperFieldConfigured: "@it@ Configured fields are improper." // show the value in comment pod when primary or foreign key field are configured improper.
    }
    //end of shared nls
});
