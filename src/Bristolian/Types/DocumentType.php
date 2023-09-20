<?php

namespace Bristolian\Types;

enum DocumentType: string
{
    case markdown_file = 'markdown_file';

    case markdown_url = 'markdown_url';
}
