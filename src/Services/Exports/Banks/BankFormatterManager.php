<?php

namespace Nuvis\FijiPayroll\Services\Exports\Banks;

use InvalidArgumentException;
use Nuvis\FijiPayroll\Models\PayrollRun;

class BankFormatterManager
{
    /** @var array<string, BankFormatterInterface> */
    protected array $formatters = [];

    public function __construct()
    {
        $this->register(new BspFormatter());
        $this->register(new AnzFormatter());
        $this->register(new HfcFormatter());
        $this->register(new BredFormatter());
        $this->register(new GenericFormatter());
    }

    public function register(BankFormatterInterface $formatter): void
    {
        $this->formatters[$formatter->key()] = $formatter;
    }

    public function get(string $key): BankFormatterInterface
    {
        if (! isset($this->formatters[$key])) {
            throw new InvalidArgumentException("No bank formatter registered for [{$key}]");
        }

        return $this->formatters[$key];
    }

    public function all(): array
    {
        return $this->formatters;
    }

    public function options(): array
    {
        $options = [];
        foreach ($this->formatters as $formatter) {
            $options[$formatter->key()] = $formatter->name();
        }
        return $options;
    }

    public function generate(string $bankKey, PayrollRun $payrollRun): array
    {
        $formatter = $this->get($bankKey);

        return [
            'content'  => $formatter->generate($payrollRun),
            'filename' => $formatter->filename($payrollRun),
            'mime'     => $formatter->mimeType(),
        ];
    }
}
