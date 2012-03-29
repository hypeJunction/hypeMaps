hypeMaps is part of the hypeJunction plugin bundle

This plugin is released under a GPL compatible license, as a courtesy of hypeJunction Club
Release of this plugin available at elgg.org might not always correspond to the latest stable release available at www.hypeJunction.com


PLUGIN DESCRIPTION
------------------
hypeMaps is a Google Maps bridge for Elgg

hypeMaps capabilities:
- Identify user's current position using user's browser settings
- Geocode user's permanent location (profile details)
- Geocode any location, address or reverse geocode coordinates (hypeFormBuilder supported input fields)
- Add location to any object in your system (back end coding will be required)
- Adds support for user places - users can create their own places with custom categories and markers


REQUIREMENTS
------------
1) Elgg 1.8.3+
2) hypeFramework 1.8.5

INTEGRATION / COMPATIBILITY
---------------------------
- hypeMaps integrates with hypeFormBuilder and thus can be used to create new location-driven forms
- hypeMaps' default places and admin defined location-driven objects can be used as sections via hypeFormBuilder (e.g. in hypePortfolio)
- Creates new input and output for location, address, coordinates

INSTALLATION
------------
- Install hypeFramework 1.8.1+
- Place hypeMaps below hypeFramework in the plugin's list and activate
- Setup default values in hypeMaps plugin settings

UPGRADING FROM PREVIOUS VERSION
-------------------------------
-- Disable all hype plugins, except hypeFramework
-- Disable hypeFramework
-- Backup your database and files
-- Remove all hype plugins from your server and upload the new version
-- Enable hypeFramework
-- Enable other hype plugins

USER GUIDE
----------
- if you have hypeFormBuilder activated, you will see hjplace in the list of available modules
- you can create custom sections/forms using hypeFormBuilder and using one of the available inputs (location, address, coordinates)

TODO
----
- add support for geometry (areas, routes, polylines)
- add location-picker

NOTES / WARNINGS
----------------
- Google Maps limitations apply
- if no $entity is passed to output views, the location is geocoded thus making another call to google maps (to prevent, pass the entity to the output)

BUG REPORTS
-----------
Bugs and feature requests can be submitted at:
http://hypeJunction.com/trac

