<?php

// Routes

$app->post('/create', function ($request, $response, $args) {
    $requestData = $request->getParsedBody();

    $model = getModel($requestData['table']);
    $item = new $model();

    if (!empty($item)) {
        foreach ($requestData['data'] as $data) {
            if (
                isset($data['checkExistence'])
                && $data['checkExistence']
                && $model::where($data['column'], $data['value'])->exists()
            ) {
                return $response->withStatus(500)
                    ->withHeader('Content-Type', 'application/json')
                    ->write(json_encode([
                        'status' => 'error',
                        'message' => "Record with {$data['column']} '{$data['value']}' already exists"
                    ]));
            }

            if (isset($data['incrementCount'])) {
                $increment = [
                  'count' => $data['incrementCount'],
                  'table' => $data['incrementTable'],
                  'column' => $data['incrementColumn'],
                  'value' => $data['value']
                ];
            }
            $item->$data['column'] = $data['value'];
        }
        if ($item->save()) {
            if (!empty($increment)) {
                $incModel = getModel($increment['table']);
                $incModel::where('id', $increment['value'])
                    ->increment($increment['column'], $increment['count']);
            }
            return $response->withHeader('Content-Type', 'application/json')
                ->write($item->toJson());
        }
    }
});

$app->post('/read', function ($request, $response, $args) {
  $requestData = $request->getParsedBody();

  $model = getModel($requestData['table']);

  if (!empty($model)) {
      $query = $model::query();
      if (isset($requestData['include'])) {
          $map = [
              'category_id' => 'Category',
              'user_id' => 'User',
              'post_id' => 'Post',
              'from_user' => 'User'
          ];

          foreach ($requestData['include'] as $field) {
              $query->with($map[$field]);
          }
      }

      if (isset($requestData['exists'])) {
          $query->where($requestData['exists'], '!=', '');
      }

      if (isset($requestData['select'])) {
          $query->select($requestData['select']);
      }

      if (isset($requestData['olderThan'])) {
          $query->where($requestData['olderThan']['column'], '>=', $requestData['olderThan']['date']);
      }

      if (isset($requestData['equalTo'])) {
          foreach ($requestData['equalTo'] as $data) {
              $query->where($data['column'], '=', $data['value']);
          }
      }

      if (isset($requestData['notEqualTo'])) {
          foreach ($requestData['notEqualTo'] as $data) {
              $query->where($data['column'], '!=', $data['value']);
          }
      }

      if (isset($requestData['search'])) {
          $query->where(function ($query) use ($requestData) {
              foreach ($requestData['search']['columns'] as $column) {
                  $query->orWhere($column, 'LIKE', '%' . $requestData['search']['text'] . '%');
              }
          });
      }

      if (isset($requestData['containedIn'])) {
          $query->where(function ($query) use ($requestData) {
              foreach ($requestData['containedIn']['value'] as $value) {
                  $query->orWhere($requestData['containedIn']['column'], '=', $value);
              }
          });
      }
      $result = $query->orderBy($requestData['descending'], 'desc')
          ->take($requestData['limit']);

      return $response->withHeader('Content-Type', 'application/json')
          ->write($result->get()->toJson());
    }
});

$app->post('/update', function ($request, $response, $args) {
    $requestData = $request->getParsedBody();
    $id = $requestData['id'];

    $model = getModel($requestData['table']);
    $item = $model::find($id);
    if (!empty($item)) {
        foreach ($requestData['data'] as $data) {
            if (isset($data['incrementCount'])) {
                $increment = [
                  'count' => $data['incrementCount'],
                  'table' => $data['incrementTable'],
                  'column' => $data['incrementColumn'],
                  'value' => $data['value']
                ];
            }
            $item->$data['column'] = $data['value'];
        }
        if ($item->save()) {
            if (!empty($increment)) {
                $incModel = getModel($increment['table']);
                $incModel::where('id', $increment['value'])
                    ->increment($increment['column'], $increment['count']);
            }
            return $response->withHeader('Content-Type', 'application/json')
                ->write($item->toJson());
        }
    }
});

$app->post('/delete', function ($request, $response, $args) {
    $requestData = $request->getParsedBody();
    $id = $requestData['id'];

    $model = getModel($requestData['table']);
    $item = $model::find($id);

    if (!empty($item)) {
        if (isset($requestData['data'])) {
            foreach ($requestData['data'] as $data) {
                if (isset($data['incrementCount'])) {
                    $map = [
                        'post' => 'category_id',
                        'comment' => 'post_id'
                    ];
                    $increment = [
                      'count' => $data['incrementCount'],
                      'table' => $data['incrementTable'],
                      'column' => $data['incrementColumn'],
                      'value' => $item->getAttribute($map[strtolower($requestData['table'])])
                    ];
                }
            }
        }


        if ($item->delete()) {
            if (!empty($increment)) {
                $incModel = getModel($increment['table']);
                $incModel::where('id', $increment['value'])
                    ->increment($increment['column'], $increment['count']);
            }
            return $response->withHeader('Content-Type', 'application/json')
                ->write(json_encode(['status' => true]));
        } else {
            return $response->withHeader('Content-Type', 'application/json')
                ->write(json_encode(['status' => false]));
        }
    } else {
        return $response->withHeader('Content-Type', 'application/json')
            ->write(json_encode(['status' => false, 'message' => 'Item not exists']));
    }
});

if (!function_exists('getModel')) {
    function getModel($table) {
        switch (strtolower($table)) {
            case 'user':
                $model = 'User';
                break;

            case 'category':
                $model = 'Category';
                break;

            case 'post':
                $model = 'Post';
                break;

            case 'comment':
                $model = 'Comment';
                break;
        }

        return $model;
    }
}
