# Craft CMS 3.x 404 monitor

Monitor 404 HTTP not found exceptions. See which requests fail often.

#### Dashboard widgets to monitor 404 web requests
![404 web requests widget](https://raw.githubusercontent.com/frontwise/craft-404monitor/master/webrequests_widget.png)

#### Summary view of 404 web requests
![404 web requests summary](https://raw.githubusercontent.com/frontwise/craft-404monitor/master/webrequests_summary.png)

#### 404 web requests
![404 web requests](https://raw.githubusercontent.com/frontwise/craft-404monitor/master/webrequests.png)

## Requirements
This plugin requires Craft CMS 3.0.21 or later.

## Installation

[Click here](INSTALL.md) for the installation readme.

## Configuration
Under settings > plugins you can configure the settings for this plugin.

### Storage period
Number of days to store the 404 web requests. Leave blank to keep forever. Defaults to `null`.

## Widget
On your Craft dashboard you can enable the widget `404 monitor`, which will display a chart with 404 hits.
You can configure the chart type and displayed period.

## Roadmap
 - Configure 301 redirects
 - Ban visitors based on IP/user agent

### Contributors & Developers
Brought to you by [Frontwise](https://frontwise.com)