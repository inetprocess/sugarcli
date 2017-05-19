<?php

require_once(__DIR__ . '/../vendor/autoload.php');

/* var_export($help); */
$default_options = array(
    'help',
    'quiet',
    'ansi',
    'no-ansi',
    'verbose',
    'version',
    'no-interaction',
);

function formatString($string)
{
    if (!is_string($string)) {
        return $string;
    }
    $mapping = array(
        '<comment>' => '**',
        '</comment>' => '**',
        '<info>' => '`',
        '</info>' => '`',
    );
    return str_replace(array_keys($mapping), array_values($mapping), $string);
}

function formatNamespaces($namespaces)
{
    foreach ($namespaces as $namespace) {
        if ($namespace['id'] != '_global') {
            print("**{$namespace['id']}:**\n\n");
        }
        foreach ($namespace['commands'] as $command) {
            print "* [$command](#".str_replace(':', '', $command).")\n";
        }
        print PHP_EOL;
    }
}

function formatArgument($name, $definition)
{
    print("* `{$definition['name']}`\t{$definition['description']}\n");
}

function formatOption($name, $definition)
{
    if (in_array($name, $GLOBALS['default_options'])) {
        return;
    }
    $shortcut = empty($definition['shortcut']) ? '': $definition['shortcut'].',';
    $value = '';
    if ($definition['accept_value']) {
        $value = '='.strtoupper($name);

        if ($definition['is_value_optional']) {
            $value = '['.$value.']';
        }
    }
    printf("* `%'. 3s %s%s`\t%s", $shortcut, $definition['name'], $value, $definition['description']);
    $default = $definition['default'];
    if ($definition['accept_value'] && $default !== null
        && (!is_array($default) || count($default))
    ) {
        printf(' **[default: `%s`]**', is_array($default) ? implode(', ', $default) : $default);
    }
    if ($definition['is_multiple']) {
        printf(' **(multiple values allowed)**');
    }
    print "\n";
}

function formatCommands($commands)
{
    foreach ($commands as $command) {
        print("`{$command['name']}`\n");
        print(str_repeat('-', strlen($command['name'])+2) . "\n\n");
        print($command['description']."\n\n");
        print("**Usage**: ".implode("\n", array_map(function ($usage) {
            return "`$usage`";
        }, $command['usage']))."\n\n");
        if ($command['help'] != $command['description']) {
            print($command['help'] . "\n\n");
        }
        if (!empty($command['definition']['arguments'])) {
            print("### Arguments\n");
            foreach ($command['definition']['arguments'] as $name => $definition) {
                formatArgument($name, $definition);
            }
            print "\n";
        }
        if (!empty($command['definition']['options'])) {
            print("### Options\n");
            foreach ($command['definition']['options'] as $name => $definition) {
                formatOption($name, $definition);
            }
            print "\n";
        }
    }
}

ob_start();
$json_help = file_get_contents('php://stdin');
$help = json_decode($json_help, true);
print ("Commands list\n");
print ("=============\n\n");
formatNamespaces($help['namespaces']);
print ("Commands details\n");
print ("=============\n\n");
formatCommands($help['commands']);
$output = ob_get_clean();
print(formatString($output));
