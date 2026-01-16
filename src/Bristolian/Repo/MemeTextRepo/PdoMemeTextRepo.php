<?php

namespace Bristolian\Repo\MemeTextRepo;

use Bristolian\Model\Generated\StoredMeme;
use Bristolian\Model\Generated\MemeText;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Database\meme_text;

class PdoMemeTextRepo implements MemeTextRepo
{
    public function __construct(
        private PdoSimple $pdo_simple
    ) {
    }

    /**
     * Stores the text for a meme.
     * 
     * @param StoredMeme $storedMeme
     * @param string $found_text
     * @return void
     */
    public function saveMemeText(
        StoredMeme $storedMeme,
        string $found_text
    ): void
    {
        $sql = meme_text::INSERT;

        $params = [
            ':text' => $found_text,
            ':meme_id' => $storedMeme->id
        ];

        $this->pdo_simple->insert($sql, $params);
    }


    /**
     * Finds the first meme that does not have a corresponding entry in the meme_text table.
     * 
     * @return StoredMeme|null
     */
    public function getNextMemeToOCR(): StoredMeme|null
    {
        $sql = <<< SQL
select 
    sm.id,                 
    sm.normalized_name,    
    sm.original_filename,  
    sm.state,              
    sm.size,               
    sm.user_id,            
    sm.created_at          
from                       
  stored_meme sm
left join 
    meme_text mt on sm.id = mt.meme_id
where 
  mt.id is null and
  sm.deleted = 0
order by 
  sm.created_at asc
limit 1
SQL;

        $meme = $this->pdo_simple->fetchOneAsObjectOrNullConstructor(
            $sql,
            [],
            StoredMeme::class
        );

        return $meme;
    }

    /**
     * Search for meme IDs by text content (case-insensitive).
     * 
     * @param string $user_id
     * @param string $search_text
     * @return array<string> Array of meme IDs
     */
    public function searchMemeIdsByText(
        string $user_id,
        string $search_text
    ): array {
        $sql = <<< SQL
SELECT DISTINCT
  sm.id
FROM
  stored_meme sm
JOIN
  meme_text mt ON sm.id = mt.meme_id
WHERE
  sm.user_id = :user_id AND
  sm.deleted = 0 AND
  LOWER(mt.text) LIKE LOWER(:search_text)
SQL;

        $escaped_text = escapeMySqlLikeString($search_text);
        $params = [
            ':user_id' => $user_id,
            ':search_text' => '%' . $escaped_text . '%'
        ];

        $meme_ids = $this->pdo_simple->fetchAllRowsAsScalar($sql, $params);
        return $meme_ids;
    }

    /**
     * Gets the text for a meme (returns the most recent entry if multiple exist).
     * 
     * @param string $meme_id
     * @return MemeText|null
     */
    public function getMemeText(string $meme_id): MemeText|null
    {
        $sql = meme_text::SELECT . <<< SQL
where
  meme_id = :meme_id
order by
  created_at desc
limit 1
SQL;

        $params = [
            ':meme_id' => $meme_id
        ];

        return $this->pdo_simple->fetchOneAsObjectOrNullConstructor(
            $sql,
            $params,
            MemeText::class
        );
    }

    /**
     * Updates the text for a meme. If text exists, updates it; if not, inserts it.
     * 
     * @param string $meme_id
     * @param string $text
     * @return void
     */
    public function updateMemeText(string $meme_id, string $text): void
    {
        // Check if text exists for this meme
        $existing = $this->getMemeText($meme_id);
        
        if ($existing !== null) {
            // Update existing entry
            $sql = meme_text::UPDATE;
            $params = [
                ':id' => $existing->id,
                ':text' => $text
            ];
            $this->pdo_simple->execute($sql, $params);
        } else {
            // Insert new entry
            $sql = meme_text::INSERT;
            $params = [
                ':meme_id' => $meme_id,
                ':text' => $text
            ];
            $this->pdo_simple->insert($sql, $params);
        }
    }
}