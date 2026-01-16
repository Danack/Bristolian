<?php

namespace Bristolian\Repo\MemeTextRepo;

use Bristolian\Model\Generated\StoredMeme;
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
  mt.id is null
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
}