#!/bin/bash
#
# Copyright 2013 Eric D. Hough (http://ehough.com)
#
# This file is part of throwback (https://github.com/ehough/throwback)
#
# This Source Code Form is subject to the terms of the Mozilla Public
# License, v. 2.0. If a copy of the MPL was not distributed with this
# file, You can obtain one at http://mozilla.org/MPL/2.0/.
#

cd $TRAVIS_BUILD_DIR

wget https://raw.github.com/ehough/throwback/develop/src/main/php/throwback.php

php throwback.php