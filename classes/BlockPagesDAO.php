<?php

/**
 * @file classes/StaticPagesDAO.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.staticPages
 *
 * @class StaticPagesDAO
 *
 * @brief Operations for retrieving and modifying StaticPages objects.
 */

namespace APP\plugins\generic\blockPages\classes;

use PKP\db\DAOResultFactory;
use PKP\db\DBResultRange;

class BlockPagesDAO extends \PKP\db\DAO
{
    /**
     * Get a static page by ID
     *
     * @param int $staticPageId Static page ID
     * @param int $contextId Optional context ID
     */
    public function getById($staticPageId, $contextId = null)
    {
        $params = [(int) $staticPageId];
        if ($contextId) {
            $params[] = (int) $contextId;
        }

        $result = $this->retrieve(
            'SELECT * FROM block_pages WHERE block_page_id = ?'
            . ($contextId ? ' AND context_id = ?' : ''),
            $params
        );
        $row = $result->current();
        return $row ? $this->_fromRow((array) $row) : null;
    }

    /**
     * Get a set of static pages by context ID
     *
     * @param int $contextId
     * @param DBResultRange $rangeInfo optional
     *
     * @return DAOResultFactory<StaticPage>
     */
    public function getByContextId($contextId, $rangeInfo = null)
    {
        $result = $this->retrieveRange(
            'SELECT * FROM block_pages WHERE context_id = ?',
            [(int) $contextId],
            $rangeInfo
        );
        return new DAOResultFactory($result, $this, '_fromRow');
    }

    /**
     * Get a static page by path.
     *
     * @param int $contextId Context ID
     * @param string $path Path
     *
     * @return BlockPage
     */
    public function getByPath($contextId, $path)
    {
        $result = $this->retrieve(
            'SELECT * FROM block_pages WHERE context_id = ? AND path = ?',
            [(int) $contextId, $path]
        );
        $row = $result->current();
        return $row ? $this->_fromRow((array) $row) : null;
    }

    /**
     * Insert a static page.
     *
     * @param BlockPage $staticPage
     *
     * @return int Inserted static page ID
     */
    public function insertObject($staticPage)
    {
        $this->update(
            'INSERT INTO block_pages (context_id, path) VALUES (?, ?)',
            [(int) $staticPage->getContextId(), $staticPage->getPath()]
        );

        $staticPage->setId($this->getInsertId());
        $this->updateLocaleFields($staticPage);

        return $staticPage->getId();
    }

    /**
     * Update the database with a static page object
     *
     * @param BlockPage $staticPage
     */
    public function updateObject($staticPage)
    {
        $this->update(
            'UPDATE	block_pages
			SET	context_id = ?,
				path = ?
			WHERE	block_page_id = ?',
            [
                (int) $staticPage->getContextId(),
                $staticPage->getPath(),
                (int) $staticPage->getId()
            ]
        );
        $this->updateLocaleFields($staticPage);
    }

    /**
     * Delete a static page by ID.
     *
     * @param int $staticPageId
     */
    public function deleteById($staticPageId)
    {
        $this->update(
            'DELETE FROM block_pages WHERE block_page_id = ?',
            [(int) $staticPageId]
        );
    }

    /**
     * Delete a static page object.
     *
     * @param BlockPage $staticPage
     */
    public function deleteObject($staticPage)
    {
        $this->deleteById($staticPage->getId());
    }

    /**
     * Generate a new static page object.
     *
     * @return BlockPage
     */
    public function newDataObject()
    {
        return new BlockPage();
    }

    /**
     * Return a new static pages object from a given row.
     *
     * @return BlockPage
     */
    public function _fromRow($row)
    {
        $staticPage = $this->newDataObject();
        $staticPage->setId($row['block_page_id']);
        $staticPage->setPath($row['path']);
        $staticPage->setContextId($row['context_id']);

        $this->getDataObjectSettings('block_page_settings', 'block_page_id', $row['block_page_id'], $staticPage);
        return $staticPage;
    }

    /**
     * Get field names for which data is localized.
     *
     * @return array
     */
    public function getLocaleFieldNames()
    {
        return ['title', 'content'];
    }

    /**
     * Update the localized data for this object
     */
    public function updateLocaleFields(&$staticPage)
    {
        $this->updateDataObjectSettings(
            'block_page_settings',
            $staticPage,
            ['block_page_id' => $staticPage->getId()]
        );
    }
}
