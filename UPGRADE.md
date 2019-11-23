# Upgrading Instructions for Yii 2.0 Redis Extension

This file contains the upgrade notes for Yii 2.0 Redis Extension. These notes highlight changes that
could break your application when you upgrade extension from one version to another.

Upgrading in general is as simple as updating your dependency in your composer.json and
running `composer update`. In a big application however there may be more things to consider,
which are explained in the following.

> Note: The following upgrading instructions are cumulative. That is,
if you want to upgrade from version A to version C and there is
version B between A and C, you need to follow the instructions
for both A and B.
