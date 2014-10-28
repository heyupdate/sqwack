<?php

namespace Sqwack\Capturer;

use Symfony\Component\Process\ProcessBuilder;

class ImagesnapCapturer
{
    public function capture($file, $delay = 2, $device = null)
    {
        $processBuilder = new ProcessBuilder(array('imagesnap', '-q', '-w', $delay));
        $processBuilder->add('-w')->add($delay);

        if ($device !== null) {
            // The device to use to capture the photo
            $processBuilder->add('-d')->add($device);
        }

        // Specify the file path to save the captured photo to
        $processBuilder->add($file);

        $process = $processBuilder->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }
    }
}
