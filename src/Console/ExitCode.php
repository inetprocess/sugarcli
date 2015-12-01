<?php

namespace SugarCli\Console;

class ExitCode
{
    // SugarCli errors [0-10]
    const EXIT_STATUS_MODIFICATIONS = 2;
    const EXIT_FORMAT_ERROR = 3;
    const EXIT_INVENTORY_ERROR = 4;

    // Sugar installer errors [11-20]
    const EXIT_NOT_EXTRACTED = 11;
    const EXIT_NOT_INSTALLED = 12;
    const EXIT_INSTALL_ERROR = 13;
    const EXIT_FILE_ALREADY_EXISTS = 14;

    // Sugar errors [20-30]
    const EXIT_UNKNOWN_SUGAR_ERROR = 20;
    const EXIT_METADATA_NOT_FOUND = 21;
    const EXIT_USER_NOT_FOUND = 22;
    const EXIT_UNKNOWN_BEAN_TYPE = 23;
}
