<?php

namespace GoDaddy\WordPress\MWC\Core\Repositories\Strategies\Contracts;

/**
 * Represents an ID mutation strategy. Classes that implement this contract will be able to format a string from the
 * database, and back into the format to save to the database.
 */
interface RemoteIdStrategyContract
{
    /**
     * Formats a remote ID that was just pulled from the database.
     *
     * @param string|null $value
     * @return string|null
     */
    public function formatRemoteIdFromDatabase(?string $value) : ?string;

    /**
     * Formats a remote ID to save or query in the database.
     *
     * @param string $value
     * @return string
     */
    public function getRemoteIdForDatabase(string $value) : string;
}
