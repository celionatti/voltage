<?php

declare(strict_types=1);

use celionatti\Voltage\Exceptions\VoltException;

/**
 * Library Name: Voltage
 * Author: Celio Natti
 * Version: 1.0.0
 * Year: 2023
 */

function addAction($hook, $callback, $priority = 10)
{
    global $volt_actions;

    $volt_actions[$hook][] = array(
        'callback' => $callback,
        'priority' => $priority
    );
}

function doAction($hook, $args = array())
{
    global $volt_actions;

    if (isset($volt_actions[$hook])) {
        // Sort actions by priority
        usort($volt_actions[$hook], function ($a, $b) {
            return $a['priority'] - $b['priority'];
        });

        foreach ($volt_actions[$hook] as $action) {
            call_user_func_array($action['callback'], $args);
        }
    }
}

function addFilter($hook, $callback, $priority = 10)
{
    global $filters;

    $filters[$hook][] = array(
        'callback' => $callback,
        'priority' => $priority
    );
}

function doFilter($hook, $value, $args = array())
{
    global $filters;

    if (isset($filters[$hook])) {
        // Sort filters by priority
        usort($filters[$hook], function ($a, $b) {
            return $a['priority'] - $b['priority'];
        });

        foreach ($filters[$hook] as $filter) {
            $value = call_user_func_array($filter['callback'], array_merge(array($value), $args));
        }
    }

    return $value;
}

function addEventListener($event, $callback, $priority = 10)
{
    global $events;

    if (!isset($events[$event])) {
        $events[$event] = array();
    }

    $events[$event][] = array(
        'callback' => $callback,
        'priority' => $priority
    );
}

function fireEvent($event, $args = array())
{
    global $events;

    if (isset($events[$event])) {
        // Sort events by priority
        usort($events[$event], function ($a, $b) {
            return $a['priority'] - $b['priority'];
        });

        foreach ($events[$event] as $eventListener) {
            call_user_func_array($eventListener['callback'], $args);
        }
    }
}

function initializeHooks()
{
    // Initialize default hooks or predefined actions/filters
    addDefaultHooks();
}

function addDefaultHooks()
{
    // Example: Adding a default action and filter
    addAction('volt_default_action', function () {
        echo 'This is a default action!';
    });

    addFilter('volt_default_filter', function ($data) {
        return $data . ' Modified by the default filter.';
    });
}

function checkHooks()
{
    global $actions, $filters;
    // Check if essential hooks are registered
    $requiredHooks = array('volt_default_action', 'volt_default_filter');

    foreach ($requiredHooks as $hook) {
        if (!isset($actions[$hook]) && !isset($filters[$hook]) && !isset($events[$hook])) {
            throw new VoltException("Required hook '$hook' is missing in checkHooks function.");
        }
    }
}

function getPackageFolders($packages_folder = 'packages/', $filter = null, $includeInfo = false, $requiredSubfolders = [])
{
    $result = [];

    // Ensure the packages folder path ends with a directory separator
    $packages_folder = rtrim($packages_folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

    $folders = scandir(dirname(__DIR__) . DIRECTORY_SEPARATOR . $packages_folder);

    if (!$folders) {
        throw new VoltException("Folders Not Found");
    }

    foreach ($folders as $folder) {
        $folderPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . $packages_folder . $folder;

        if ($folder != '.' && $folder != '..' && is_dir($folderPath)) {
            // Check if the package meets the filtering criteria
            if ($filter === null || call_user_func($filter, $folderPath)) {
                $packageInfo = [
                    'name' => $folder,
                    'path' => $folderPath,
                    'files' => [],
                    'subfolders' => [],
                ];

                // Include additional information about the plugin if requested
                if ($includeInfo) {
                    $packageInfo['files'] = getPackageFiles($folderPath);
                    $packageInfo['subfolders'] = getPackageSubfolders($folderPath);

                    // Check if the required subfolders are present in the package
                    $missingRequiredSubfolders = array_diff($requiredSubfolders, $packageInfo['subfolders']);
                    if (!empty($missingRequiredSubfolders)) {
                        $missingSubfoldersList = implode(', ', $missingRequiredSubfolders);
                        throw new VoltException("Error: Missing required subfolder(s) in package '$folder': $missingSubfoldersList");
                    }
                }

                $result[] = $packageInfo;
            }
        }
    }

    return $result;
}

function loadPackages($packages_folder = 'packages/', $filter = null, $includeInfo = false, $requiredSubfolders = [])
{
    $packages = getPackageFolders($packages_folder, $filter, $includeInfo, $requiredSubfolders);
    $loadedPackages = [];
    $existingIds = [];

    foreach ($packages as $package) {
        $installJsonPath = $package['path'] . DIRECTORY_SEPARATOR . 'install.json';

        // Check if install.json exists for the package
        if (file_exists($installJsonPath)) {
            // Read and decode install.json
            $installJsonContent = file_get_contents($installJsonPath);
            $installData = json_decode($installJsonContent, true);

            // Check if JSON decoding was successful
            if (json_last_error() === JSON_ERROR_NONE) {
                // Check for the 'active' property
                if (isset($installData['active']) && $installData['active'] !== true) {
                    continue; // Skip inactive packages
                }

                // Check if required fields are not empty
                $requiredFields = ['version', 'name', 'author', 'id'];
                foreach ($requiredFields as $field) {
                    if (empty($installData[$field])) {
                        throw new VoltException("Error: '$field' is empty in install.json for package '{$package['name']}'");
                    }
                }

                // Check if the version follows the format x.y.z
                $versionPattern = '/^\d+\.\d+\.\d+$/';
                if (!preg_match($versionPattern, $installData['version'])) {
                    throw new VoltException("Error: Invalid version format in install.json for package '{$package['name']}'. The version must follow the format x.y.z, where x, y, and z are non-negative integers");
                }

                // Check for uniqueness of id values
                if (in_array($installData['id'], $existingIds)) {
                    throw new VoltException("Error: Duplicate id '{$installData['id']}' found in install.json for package '{$package['name']}'");
                }

                // Check dependencies
                if (!empty($installData['dependencies'])) {
                    foreach ($installData['dependencies'] as $dependency => $requiredVersion) {
                        // Check if the required dependency exists
                        $dependencyExists = false;
                        foreach ($loadedPackages as $loadedPackage) {
                            if ($loadedPackage['install_data']['name'] === $dependency) {
                                $dependencyExists = true;

                                // Check if the version matches the required version
                                $loadedVersion = $loadedPackage['install_data']['version'] ?? null;
                                if ($loadedVersion && version_compare($loadedVersion, $requiredVersion, '<')) {
                                    throw new VoltException("Error: Package '{$package['name']}' requires version $requiredVersion or higher of '$dependency', but loaded version is $loadedVersion");
                                }

                                break;
                            }
                        }

                        // Throw an error if the required dependency is not loaded
                        if (!$dependencyExists) {
                            throw new VoltException("Error: Package '{$package['name']}' requires '$dependency', but it is not loaded");
                        }
                    }
                }

                // Add the loaded package to the result array with named keys
                $uniqueId = uniqid('package_');
                $loadedPackages[$uniqueId] = [
                    'package_info' => $package,
                    'install_data' => $installData,
                    'unique_id' => $uniqueId,
                ];

                // Add the id to the existing ids array
                $existingIds[] = $installData['id'];
            } else {
                // Handle JSON decoding error
                // You might want to log an error or handle it in a way suitable for your application
                throw new VoltException("Error decoding install.json for package '{$package['name']}': " . json_last_error_msg());
            }
        }
    }

    // Sort loaded packages based on their index value
    usort($loadedPackages, function ($a, $b) {
        return $a['install_data']['index'] <=> $b['install_data']['index'];
    });

    // Include required plugin files in the sorted order
    foreach ($loadedPackages as $loadedPackage) {
        $pluginFilePath = $loadedPackage['package_info']['path'] . DIRECTORY_SEPARATOR . 'plugin.php';
        if (file_exists($pluginFilePath)) {
            require_once $pluginFilePath;
        } else {
            throw new VoltException("Error: Plugin file 'plugin.php' not found in package '{$loadedPackage['package_info']['name']}'");
        }
    }

    return $loadedPackages;
}

// Helper function to get files within a package folder
function getPackageFiles($packagePath)
{
    $files = scandir($packagePath);
    $result = [];

    foreach ($files as $file) {
        $filePath = $packagePath . DIRECTORY_SEPARATOR . $file;

        if (is_file($filePath)) {
            $result[] = $file;
        }
    }

    return $result;
}

// Helper function to get subfolders within a package folder
function getPackageSubfolders($packagePath)
{
    $subfolders = scandir($packagePath);
    $result = [];

    foreach ($subfolders as $subfolder) {
        $subfolderPath = $packagePath . DIRECTORY_SEPARATOR . $subfolder;

        if ($subfolder != '.' && $subfolder != '..' && is_dir($subfolderPath)) {
            $result[] = $subfolder;
        }
    }

    return $result;
}

/**
 * Get Commented Info from File
 *
 * @param string $file_path The path to the file.
 * @return array An array of commented lines.
 */
function commentedInfo($file_path)
{
    $commented_lines = array();
    $inside_comment_block = false;

    // Open the file for reading
    $file_handle = fopen($file_path, 'r');

    if ($file_handle) {
        // Read the file line by line
        while (($line = fgets($file_handle)) !== false) {
            $trimmed_line = trim($line);

            // Check if the line starts a comment block (/**)
            if (strpos($trimmed_line, '/**') === 0) {
                $inside_comment_block = true;
                $commented_lines[] = $trimmed_line;
            }

            // Check if the line is inside a comment block
            if ($inside_comment_block) {
                $commented_lines[] = $trimmed_line;
            }

            // Check if the line ends a comment block (*/)
            if (strpos($trimmed_line, '*/') !== false) {
                $inside_comment_block = false;
            }
        }

        // Close the file handle
        fclose($file_handle);
    }

    return $commented_lines;
}

/**
 * Get Plugin Commented Lines from File
 *
 * @param string $file_path The path to the file.
 * @return stdClass An object with properties based on the commented lines.
 */
function getPluginInfo($file_path)
{
    $commented_data = new stdClass();
    ;
    $inside_comment_block = false;

    // Open the file for reading
    $file_handle = fopen($file_path, 'r');

    if ($file_handle) {
        // Read the file line by line
        while (($line = fgets($file_handle)) !== false) {
            $trimmed_line = trim($line);

            // Check if the line starts a comment block (/**)
            if (strpos($trimmed_line, '/**') === 0) {
                $inside_comment_block = true;
            }

            // Check if the line is inside a comment block
            if ($inside_comment_block) {
                // Check if the line ends a comment block (*/)
                if (strpos($trimmed_line, '*/') !== false) {
                    $inside_comment_block = false;
                } else {
                    // Split the line into key and value based on ":"
                    $parts = explode(':', $trimmed_line, 2);

                    // Check if the exploded array has at least two elements
                    if (count($parts) === 2) {
                        // Remove leading asterisks and trim
                        $key = trim(str_replace('*', '', $parts[0]));
                        $value = trim($parts[1]);

                        // Set the property in the commented_data object
                        $commented_data->{$key} = $value;
                    }
                }
            }
        }

        // Close the file handle
        fclose($file_handle);
    }

    return $commented_data;
}

function getPackageId($package_name, $packages_folder = 'packages/')
{
    $packages = getPackageFolders($packages_folder);

    foreach ($packages as $package) {
        $installJsonPath = $package['path'] . DIRECTORY_SEPARATOR . 'install.json';

        // Check if install.json exists for the package
        if (file_exists($installJsonPath)) {
            // Read and decode install.json
            $installJsonContent = file_get_contents($installJsonPath);
            $installData = json_decode($installJsonContent, true);

            // Check if JSON decoding was successful
            if (json_last_error() === JSON_ERROR_NONE) {
                // Check for the 'active' property
                if (isset($installData['active']) && $installData['active'] !== true) {
                    continue; // Skip inactive packages
                }

                // Check if the ID matches the desired package ID
                if (isset($installData['name']) && $installData['name'] === $package_name) {
                    return $installData['id'];
                }
            } else {
                // Handle JSON decoding error
                // You might want to log an error or handle it in a way suitable for your application
                throw new VoltException("Error decoding install.json for package '{$package['name']}': " . json_last_error_msg());
            }
        }
    }

    // If the package with the specified ID is not found
    return null;
}