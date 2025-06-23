<?php

namespace Bristolian\Model;

use Bristolian\DataType\PropertyType\BasicString;
use Bristolian\Types\DocumentType;
use Bristolian\Types\UserList;
use DataType\Create\CreateArrayOfTypeFromArray;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\SafeAccess;

class UserDocument implements DataType
{
    use SafeAccess;
    use CreateFromVarMap;
    use CreateArrayOfTypeFromArray;
    use GetInputTypesFromAttributes;

    public readonly DocumentType $type;

    private User|null $user;

    public function __construct(
        #[BasicString('type')]
        public readonly string $string_type,
        #[BasicString('title')]
        public readonly string $title,
        #[BasicString('source')]
        public readonly string $source,
        //        #[BasicString('source')]
        //        public readonly User $user
    ) {
        $document_type = DocumentType::tryFrom($string_type);

        $this->user = new User(UserList::sid->value);

        if ($document_type === null) {
            throw new \Exception("Unknown document type '" . $string_type . "'");
        }
        $this->type = $document_type;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }
}
