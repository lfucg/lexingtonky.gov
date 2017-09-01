<%@ WebHandler Language="C#" Class="ICalender" %>

using System;
using System.Web;

public class ICalender : IHttpHandler
{
    public void ProcessRequest(HttpContext ctx)
    {

        ctx.Response.ContentType = "text/calendar";
        /*
         * These are the local variables which will grab the parameters from  query string.
         */
        int events = Convert.ToInt32(ctx.Request.QueryString["events"]);
        string[] startDate = new string[events];
        string[] endDate = new string[events];
        string[] filename = new string[events];
        string[] summary = new string[events];
        string[] organizer = new string[events];
        string[] description = new string[events];
        string[] location = new string[events];

        // Setting attribute values in case of multiple Events
        for (int i = 0; i < events; i++)
        {
            startDate[i] = ctx.Request.QueryString["sd" + i];
            endDate[i] = ctx.Request.QueryString["ed" + i];
            filename[i] = ctx.Request.QueryString["fn" + i];
            summary[i] = ctx.Request.QueryString["sum" + i];
            organizer[i] = ctx.Request.QueryString["org" + i];
            description[i] = ctx.Request.QueryString["des" + i];
            location[i] = ctx.Request.QueryString["loc" + i];
        }
        //If file name is nothing then it ICS file should take default file name.
        if (events > 1)
        {
            ctx.Response.AddHeader("Content-disposition", "attachment; filename = Events.ics");
        }
        else
        {
            ctx.Response.AddHeader("Content-disposition", "attachment; filename = " + filename[0] + ".ics");
        }
        ctx.Response.Write("BEGIN:VCALENDAR");
        ctx.Response.Write("\nVERSION:2.0");
        ctx.Response.Write("\nMETHOD:PUBLISH");
        // Grouping for multiple Events
        for (int i = 0; i < events; i++)
        {
            ctx.Response.Write("\nBEGIN:VEVENT");
            //If Organizer is defined then only it will add it to ICS file.
            if (organizer[i] != "")
            {
                ctx.Response.Write("\nORGANIZER:MAILTO:" + organizer[i]);
            }
            // If Start Date and End Date is nothing then it should take current Date as a start date and no end date so that it will create all day event.
            ctx.Response.Write("\nDTSTART:" + startDate[i]);
            ctx.Response.Write("\nDTEND:" + endDate[i]);
            ctx.Response.Write("\nLOCATION:" + location[i]);
            ctx.Response.Write("\nSUMMARY:" + summary[i]);
            ctx.Response.Write("\nDESCRIPTION:" + description[i]);
            ctx.Response.Write("\nPRIORITY:5");
            ctx.Response.Write("\nCLASS:PUBLIC");
            ctx.Response.Write("\nEND:VEVENT");
        }
        ctx.Response.Write("\nEND:VCALENDAR");
        ctx.Response.End();
    }
    public bool IsReusable
    {
        get
        {
            return false;
        }
    }
}