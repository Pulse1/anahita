<?php

/**
 * Default Entity Serializer.
 *
 * @category   Anahita
 *
 * @author     Arash Sanieyan <ash@anahitapolis.com>
 * @author     Rastin Mehr <rastin@anahitapolis.com>
 * @license    GNU GPLv3 <http://www.gnu.org/licenses/gpl-3.0.html>
 *
 * @link       http://www.GetAnahita.com
 */
class ComBaseDomainSerializerDefault extends AnDomainSerializerDefault
{
    /**
     * {@inheritdoc}
     */
    public function toSerializableArray($entity)
    {
        $data = new KConfig();

        $viewer = $this->getService('com:people.viewer');

        $data[$entity->getIdentityProperty()] = $entity->getIdentityId();

        $data['objectType'] = 'com.'.$entity->getIdentifier()->package.'.'.$entity->getIdentifier()->name;

        if ($entity->isDescribable()) {
            $data['name'] = $entity->name;
            $data['body'] = $entity->body;
            $data['alias'] = $entity->alias;
        }

        if ($entity->inherits('ComBaseDomainEntityComment')) {
            $data['body'] = $entity->body;
        }

        if ($entity->isPortraitable()) {
            $imageURL = array();

            if ($entity->portraitSet()) {
                $sizes = $entity->getPortraitSizes();
                foreach ($sizes as $name => $size) {
                    $url = $entity->getPortraitURL($name);
                    $parts = explode('x', $size);
                    $width = 0;
                    $height = 0;

                    if (count($parts) == 0) {
                        continue;
                    } elseif (count($parts) == 1) {
                        $height = $width = $parts[0];
                    } else {
                        $width = $parts[0];
                        $height = $parts[1];
                        //hack to set the ratio based on the original
                        if ($height == 'auto' && isset($sizes['original'])) {
                            $original_size = explode('x', $sizes['original']);
                            $height = ($width * $original_size[1]) / $original_size[0];
                        }
                    }

                    $imageURL[$name] = array(
                        'size' => array('width' => (int) $width,'height' => (int) $height),
                        'url' => $url,
                    );
                }
            }

            $data['imageURL'] = $imageURL;
        }
        
        if ($entity->isCoverable()) {
            $coverURL = array();
            
            if ($entity->coverSet()) {
                $coverSizes = $entity->getCoverSizes();
                foreach ($coverSizes as $name => $size) {
                    $url = $entity->getCoverURL($name);
                    $parts = explode('x', $size);
                    $width = 0;
                    $height = 0;

                    if (count($parts) == 0) {
                        continue;
                    } elseif (count($parts) == 1) {
                        $height = $width = $parts[0];
                    } else {
                        $width = $parts[0];
                        $height = $parts[1];
                        //hack to set the ratio based on the original
                        if ($height == 'auto' && isset($sizes['original'])) {
                            $original_size = explode('x', $sizes['original']);
                            $height = ($width * $original_size[1]) / $original_size[0];
                        }
                    }

                    $coverURL[$name] = array(
                        'size' => array('width' => (int) $width,'height' => (int) $height),
                        'url' => $url,
                    );
                }
            }
            
            $data['coverURL'] = $coverURL;
        }

        if ($entity->isModifiable()) {
            $data->append(array(
                'author' => null,
                'creationTime' => null,
                'editor' => null,
                'updateTime' => null,
            ));
            
            $data['creationTime'] = $entity->creationTime->getDate();
            $data['updateTime'] = $entity->updateTime->getDate();
            
            if (!is_person($entity)) {
                if (isset($entity->author)) {
                    $data['author'] = $entity->author->toSerializableArray();
                }
                
                if (isset($entity->editor)) {
                    $data['editor'] = $entity->editor->toSerializableArray();
                }
            }
        }

        if ($entity->isCommentable()) {
            $data['openToComment'] = (bool) $entity->openToComment;
            $data['numOfComments'] = $entity->numOfComments;
            $data['lastCommentTime'] = $entity->lastCommentTime ? $entity->lastCommentTime->getDate() : null;
            $data['lastComment'] = null;
            $data['lastCommenter'] = null;

            if (isset($entity->lastComment)) {
                $data['lastComment'] = $entity->lastComment->toSerializableArray();
            }

            if (isset($entity->lastCommenter)) {
                $data['lastCommenter'] = $entity->lastCommenter->toSerializableArray();
            }
        }

        if ($entity->isSubscribable()) {
            $data['subscriberCount'] = $entity->subscriberCount;
        }

        if ($entity->isVotable()) {
            $data['voteUpCount'] = $entity->voteUpCount;
        }

        if (!is_person($entity) && $entity->isOwnable()) {
            $data['owner'] = $entity->owner->toSerializableArray();
        }

        if ($entity->inherits('ComLocationsDomainEntityLocation')) {
            $data['longitude'] = $entity->geoLongitude;
            $data['latitude'] = $entity->geoLatitude;
        }

        return KConfig::unbox($data);
    }
}
