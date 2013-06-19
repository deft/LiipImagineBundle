<?php

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FilesystemResolver extends AbstractFilesystemResolver
{
    /**
     * Return the local filepath.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @param string $path The resource path to convert.
     * @param string $filter The name of the imagine filter.
     *
     * @return string
     */
    protected function getFilePath($path, $filter)
    {
        return $this->basePath . DIRECTORY_SEPARATOR . $filter . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * Resolves filtered path for rendering in the browser.
     *
     * @param Request $request The request made against a _imagine_* filter route.
     * @param string  $path The path where the resolved file is expected.
     * @param string  $filter The name of the imagine filter in effect.
     *
     * @return string|Response The target path to be used in other methods of this Resolver,
     *                         a Response may be returned to avoid calling store upon resolution.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException In case the path can not be resolved.
     */
    function resolve(Request $request, $path, $filter)
    {
        $filePath = $this->getFilePath($path, $filter);

        return file_exists($filePath)
            ? new BinaryFileResponse(new \SplFileInfo($filePath))
            : $filePath;
    }

    /**
     * Returns a web accessible URL.
     *
     * @param string $path The path where the resolved file is expected.
     * @param string $filter The name of the imagine filter in effect.
     * @param bool   $absolute Whether to generate an absolute URL or a relative path is accepted.
     *                       In case the resolver does not support relative paths, it may ignore this flag.
     *
     * @return string
     */
    function getBrowserPath($path, $filter, $absolute = false)
    {
        return $this->cacheManager->generateUrl($path, $filter, $absolute);
    }

    /**
     * Clear the CacheResolver cache.
     *
     * @param string $cachePrefix The cache prefix as defined in the configuration.
     *
     * @return void
     */
    function clear($cachePrefix)
    {
        $cachePath = $this->basePath . DIRECTORY_SEPARATOR . $cachePrefix;

        if (is_dir($cachePath)) {
            $this->filesystem->remove(Finder::create()->in($cachePath)->depth(0)->directories());
        }
    }
}
