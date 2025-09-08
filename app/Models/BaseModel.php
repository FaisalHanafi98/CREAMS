<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use App\Exceptions\CREAMSException;
use App\Exceptions\DatabaseException;
use Exception;

abstract class BaseModel extends Model
{
    /**
     * Safe create with comprehensive error handling
     */
    public static function safeCreate(array $attributes)
    {
        try {
            Log::channel('activity')->info('Creating new record', [
                'model' => static::class,
                'attributes' => array_keys($attributes),
                'user_id' => session('id')
            ]);

            return static::create($attributes);

        } catch (QueryException $e) {
            Log::channel('database')->error('Database error during record creation', [
                'model' => static::class,
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'user_id' => session('id')
            ]);

            throw new DatabaseException(
                "Failed to create " . class_basename(static::class) . ": " . $e->getMessage(),
                "Unable to save the record. Please try again or contact support.",
                ['model' => static::class, 'attributes' => array_keys($attributes)]
            );

        } catch (Exception $e) {
            Log::channel('application')->error('Unexpected error during record creation', [
                'model' => static::class,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);

            throw new CREAMSException(
                "Unexpected error creating " . class_basename(static::class) . ": " . $e->getMessage(),
                "An unexpected error occurred. Please try again.",
                'MODEL_CREATE_ERROR',
                ['model' => static::class]
            );
        }
    }

    /**
     * Safe update with comprehensive error handling
     */
    public function safeUpdate(array $attributes)
    {
        try {
            Log::channel('activity')->info('Updating record', [
                'model' => static::class,
                'id' => $this->id ?? 'unknown',
                'attributes' => array_keys($attributes),
                'user_id' => session('id')
            ]);

            return $this->update($attributes);

        } catch (QueryException $e) {
            Log::channel('database')->error('Database error during record update', [
                'model' => static::class,
                'id' => $this->id ?? 'unknown',
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'user_id' => session('id')
            ]);

            throw new DatabaseException(
                "Failed to update " . class_basename(static::class) . ": " . $e->getMessage(),
                "Unable to update the record. Please try again or contact support.",
                ['model' => static::class, 'id' => $this->id ?? 'unknown']
            );

        } catch (Exception $e) {
            Log::channel('application')->error('Unexpected error during record update', [
                'model' => static::class,
                'id' => $this->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);

            throw new CREAMSException(
                "Unexpected error updating " . class_basename(static::class) . ": " . $e->getMessage(),
                "An unexpected error occurred. Please try again.",
                'MODEL_UPDATE_ERROR',
                ['model' => static::class, 'id' => $this->id ?? 'unknown']
            );
        }
    }

    /**
     * Safe delete with comprehensive error handling
     */
    public function safeDelete()
    {
        try {
            Log::channel('activity')->info('Deleting record', [
                'model' => static::class,
                'id' => $this->id ?? 'unknown',
                'user_id' => session('id')
            ]);

            return $this->delete();

        } catch (QueryException $e) {
            Log::channel('database')->error('Database error during record deletion', [
                'model' => static::class,
                'id' => $this->id ?? 'unknown',
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'user_id' => session('id')
            ]);

            // Check for foreign key constraint violation
            if (str_contains($e->getMessage(), 'foreign key constraint')) {
                throw new DatabaseException(
                    "Cannot delete " . class_basename(static::class) . " due to related data",
                    "Cannot delete this record because it has related data. Please remove related records first.",
                    ['model' => static::class, 'id' => $this->id ?? 'unknown']
                );
            }

            throw new DatabaseException(
                "Failed to delete " . class_basename(static::class) . ": " . $e->getMessage(),
                "Unable to delete the record. Please try again or contact support.",
                ['model' => static::class, 'id' => $this->id ?? 'unknown']
            );

        } catch (Exception $e) {
            Log::channel('application')->error('Unexpected error during record deletion', [
                'model' => static::class,
                'id' => $this->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);

            throw new CREAMSException(
                "Unexpected error deleting " . class_basename(static::class) . ": " . $e->getMessage(),
                "An unexpected error occurred. Please try again.",
                'MODEL_DELETE_ERROR',
                ['model' => static::class, 'id' => $this->id ?? 'unknown']
            );
        }
    }

    /**
     * Safe find with error handling
     */
    public static function safeFind($id, $columns = ['*'])
    {
        try {
            $record = static::find($id, $columns);
            
            if (!$record) {
                throw new CREAMSException(
                    class_basename(static::class) . " with ID {$id} not found",
                    "The requested record was not found.",
                    'RECORD_NOT_FOUND',
                    ['model' => static::class, 'id' => $id]
                );
            }

            return $record;

        } catch (QueryException $e) {
            Log::channel('database')->error('Database error during record lookup', [
                'model' => static::class,
                'id' => $id,
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);

            throw new DatabaseException(
                "Database error finding " . class_basename(static::class) . ": " . $e->getMessage(),
                "Unable to retrieve the record. Please try again.",
                ['model' => static::class, 'id' => $id]
            );

        } catch (CREAMSException $e) {
            // Re-throw CREAMS exceptions
            throw $e;

        } catch (Exception $e) {
            Log::channel('application')->error('Unexpected error during record lookup', [
                'model' => static::class,
                'id' => $id,
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);

            throw new CREAMSException(
                "Unexpected error finding " . class_basename(static::class) . ": " . $e->getMessage(),
                "An unexpected error occurred. Please try again.",
                'MODEL_FIND_ERROR',
                ['model' => static::class, 'id' => $id]
            );
        }
    }

    /**
     * Safe bulk insert with error handling
     */
    public static function safeBulkInsert(array $records)
    {
        try {
            Log::channel('activity')->info('Bulk inserting records', [
                'model' => static::class,
                'count' => count($records),
                'user_id' => session('id')
            ]);

            return static::insert($records);

        } catch (QueryException $e) {
            Log::channel('database')->error('Database error during bulk insert', [
                'model' => static::class,
                'count' => count($records),
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'user_id' => session('id')
            ]);

            throw new DatabaseException(
                "Failed to bulk insert " . class_basename(static::class) . " records: " . $e->getMessage(),
                "Unable to save multiple records. Please try again or contact support.",
                ['model' => static::class, 'count' => count($records)]
            );

        } catch (Exception $e) {
            Log::channel('application')->error('Unexpected error during bulk insert', [
                'model' => static::class,
                'count' => count($records),
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);

            throw new CREAMSException(
                "Unexpected error during bulk insert: " . $e->getMessage(),
                "An unexpected error occurred. Please try again.",
                'MODEL_BULK_INSERT_ERROR',
                ['model' => static::class, 'count' => count($records)]
            );
        }
    }

    /**
     * Log model events for audit trail
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            Log::channel('activity')->info('Record created', [
                'model' => get_class($model),
                'id' => $model->id ?? 'unknown',
                'user_id' => session('id'),
                'created_at' => $model->created_at
            ]);
        });

        static::updated(function ($model) {
            Log::channel('activity')->info('Record updated', [
                'model' => get_class($model),
                'id' => $model->id ?? 'unknown',
                'user_id' => session('id'),
                'updated_at' => $model->updated_at,
                'dirty_fields' => array_keys($model->getDirty())
            ]);
        });

        static::deleted(function ($model) {
            Log::channel('activity')->info('Record deleted', [
                'model' => get_class($model),
                'id' => $model->id ?? 'unknown',
                'user_id' => session('id'),
                'deleted_at' => now()
            ]);
        });
    }

    /**
     * Handle relationship loading errors
     */
    public function safeLoad($relations)
    {
        try {
            return $this->load($relations);
        } catch (Exception $e) {
            Log::channel('database')->error('Error loading model relationships', [
                'model' => get_class($this),
                'id' => $this->id ?? 'unknown',
                'relations' => is_array($relations) ? $relations : [$relations],
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);

            throw new DatabaseException(
                "Failed to load related data: " . $e->getMessage(),
                "Unable to load all related data. Some information may be missing.",
                ['model' => get_class($this), 'relations' => $relations]
            );
        }
    }
}