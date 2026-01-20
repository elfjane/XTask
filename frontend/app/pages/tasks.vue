<template>
  <div class="page">
    <div class="header">
      <h1>{{ $t('tasks.title') }}</h1>
      <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <button 
          @click="isReviewMode = !isReviewMode" 
          :class="isReviewMode ? 'btn-secondary' : 'btn-primary'" 
        >
          {{ isReviewMode ? $t('tasks.exitReview') : $t('tasks.reviewTasks') }}
        </button>
        <button 
          @click="toggleCompletedMode" 
          :class="isCompletedMode ? 'btn-secondary' : 'btn-primary'" 
        >
          {{ isCompletedMode ? $t('tasks.exitCompleted') : $t('tasks.completedTasks') }}
        </button>
        
        <!-- Search box -->
        <input 
          v-model="searchQuery" 
          @input="handleSearch"
          type="text"
          :placeholder="$t('tasks.searchPlaceholder')"
          class="search-input"
        />
        
        <!-- Sort options for completed tasks -->
        <select v-if="isCompletedMode" v-model="sortOrder" @change="fetchCompletedTasks" class="sort-select">
          <option value="desc">{{ $t('tasks.sortDesc') }}</option>
          <option value="asc">{{ $t('tasks.sortAsc') }}</option>
        </select>
        
        <!-- Sort options for regular/review tasks -->
        <select v-if="!isCompletedMode" v-model="sortField" @change="handleSortChange" class="sort-select">
          <option value="id">{{ $t('tasks.sortById') }}</option>
          <option value="level">{{ $t('tasks.sortByLevel') }}</option>
          <option value="status">{{ $t('tasks.sortByStatus') }}</option>
          <option value="project">{{ $t('tasks.sortByProject') }}</option>
          <option value="department">{{ $t('tasks.sortByDepartment') }}</option>
          <option value="points">{{ $t('tasks.sortByPoints') }}</option>
          <option value="release_date">{{ $t('tasks.sortByReleaseDate') }}</option>
          <option value="start_date">{{ $t('tasks.sortByStartDate') }}</option>
          <option value="expected_finish_date">{{ $t('tasks.sortByExpectedFinish') }}</option>
          <option value="actual_finish_date">{{ $t('tasks.sortByActualFinish') }}</option>
        </select>
        
        <select v-if="!isCompletedMode" v-model="sortOrderRegular" @change="handleSortChange" class="sort-select-mini">
          <option value="desc">↓</option>
          <option value="asc">↑</option>
        </select>
        
        <button @click="showCreateModal = true" class="btn-primary" v-if="!isReviewMode && !isCompletedMode">{{ $t('tasks.addTask') }}</button>
      </div>
    </div>

    <div v-if="pending" class="loading">{{ $t('login.loggingIn') ? $t('login.loggingIn') : 'Loading...' }}</div>
    <div v-else-if="error" class="error">{{ error.message }}</div>
    
    <div v-else class="content-wrapper">
      <!-- Regular Tasks View -->
      <div v-if="!isCompletedMode">
      <!-- Desktop View (Table) -->
      <div class="desktop-view">
        <table class="task-table">
          <thead>
            <tr>
              <th v-if="canDrag" style="width: 40px;"></th>
              <th>{{ $t('tasks.idx') }}</th>
              <th>{{ $t('tasks.level') }}</th>
              <th>{{ $t('tasks.status') }}</th>
              <th>{{ $t('tasks.assignee') }}</th>
              <th>{{ $t('tasks.relatedPersonnel') }}</th>
              <th>{{ $t('tasks.project') }}</th>
              <th>{{ $t('tasks.item') }}</th>
              <th>{{ $t('tasks.department') }}</th>
              <th>{{ $t('tasks.work') }}</th>
              <th>{{ $t('tasks.points') }}</th>
              <th>{{ $t('tasks.releaseDate') }}</th>
              <th>{{ $t('tasks.startDate') }}</th>
              <th>{{ $t('tasks.expectedFinishDate') }}</th>
              <th>{{ $t('tasks.actualFinishDate') }}</th>
              <th>{{ $t('tasks.outputUrl') }}</th>
              <th>{{ $t('tasks.memo') }}</th>
            </tr>
          </thead>
          <draggable 
            v-model="tasks" 
            tag="tbody" 
            item-key="id" 
            @end="handleReorder"
            :disabled="!canDrag"
            handle=".drag-handle"
            ghost-class="sortable-ghost"
          >
            <template #item="{ element: item }">
              <tr>
                <td v-if="canDrag" class="drag-handle">☰</td>
                <td>{{ item.id }}</td>
                <td :class="getLevelClass(item.level)">{{ getLevelLabel(item.level) }}</td>
                <td>
                  <div v-if="editingStatusId === item.id" class="edit-select-wrapper" @click.stop>
                    <select 
                      :value="item.status" 
                      @change="updateStatus(item, ($event.target as HTMLSelectElement).value)"
                      @blur="editingStatusId = null"
                      :ref="el => setFocus(el)"
                    >
                      <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                        {{ opt.label }}
                      </option>
                    </select>
                  </div>
                  <span v-else 
                    :class="['status-badge', item.status.replace(' ', '-'), 'clickable']"
                    @click.stop="startEditingStatus(item)"
                  >
                    {{ $t(`tasks.${item.status.replace(' ', '_')}`) }}
                  </span>
                </td>
                <td class="user-cell clickable" @click="startEditingAssignee(item)">
                  <div v-if="editingAssigneeId === item.id" class="edit-select-wrapper" @click.stop>
                    <select 
                      :value="item.user_id" 
                      @change="updateAssignee(item, ($event.target as HTMLSelectElement).value)"
                      @blur="editingAssigneeId = null"
                      :ref="el => setFocus(el)"
                    >
                      <option v-for="opt in userOptions" :key="opt.value" :value="opt.value">
                        {{ opt.label }}
                      </option>
                    </select>
                  </div>
                  <div v-else class="user-info">
                    <img v-if="item.assignee" :src="getAvatarUrl(item.assignee)" class="avatar" width="28" height="28" />
                    {{ item.assignee?.name || '-' }}
                  </div>
                </td>
                <td>{{ item.related_personnel || '-' }}</td>
                <td>{{ item.project }}</td>
                <td class="clickable" @click="openDetails(item)">{{ item.item || '-' }}</td>
                <td>{{ item.department }}</td>
                <td class="work-cell clickable" @click="openDetails(item)">{{ item.work }}</td>
                <td>{{ item.points }}</td>
                <td>{{ formatDate(item.release_date) }}</td>
                <td>{{ formatDate(item.start_date) }}</td>
                <td>{{ formatDate(item.expected_finish_date) }}</td>
                <td>{{ formatDate(item.actual_finish_date) }}</td>
                <td class="output-url-cell">
                  <div v-if="item.output_url">
                    <MarkdownViewer :content="item.output_url" />
                  </div>
                  <span v-else>-</span>
                </td>
                <td class="memo-cell">
                  <div v-if="item.memo" class="main-memo">
                    <MarkdownViewer :content="item.memo" />
                  </div>
                  <div class="memo-list" v-if="item.latest_remark">
                    <div class="memo-item">
                      <span class="memo-user">{{ item.latest_remark.user_name }}:</span>
                      <MarkdownViewer class="memo-content" :content="item.latest_remark.content" />
                    </div>
                  </div>
                  <div class="memo-add">
                    <input 
                      v-model="newRemarks[item.id]" 
                      :placeholder="$t('schedules.addMemoPlaceHolder')" 
                      @keyup.enter="handlePostRemark(item.id)"
                    />
                    <button @click="handlePostRemark(item.id)" :disabled="postingRemark === item.id">
                      {{ postingRemark === item.id ? '...' : '➔' }}
                    </button>
                  </div>
                </td>
              </tr>
            </template>
          </draggable>
        </table>
      </div>

      <!-- Mobile View (Cards) -->
      <div class="mobile-view">
        <div v-for="item in tasks" :key="item.id" class="card">
          <div class="card-header">
            <span class="idx">#{{ item.id }}</span>
            <span :class="['status', item.status.replace(' ', '-')]">{{ $t(`tasks.${item.status.replace(' ', '_')}`) }}</span>
          </div>
          <div class="card-body">
            <h2 class="work-title clickable" @click="openDetails(item)">{{ item.work }}</h2>
            <div class="info-grid">
              <div class="info-item"><strong>{{ $t('tasks.project') }}:</strong> {{ item.project }}</div>
              <div class="info-item"><strong>{{ $t('tasks.assignee') }}:</strong> {{ item.assignee?.name || '-' }}</div>
              <div class="info-item"><strong>{{ $t('tasks.level') }}:</strong> <span :class="['level-text', getLevelClass(item.level)]">{{ getLevelLabel(item.level) }}</span></div>
              <div class="info-item"><strong>{{ $t('tasks.expectedFinishDate') }}:</strong> {{ item.expected_finish_date || '-' }}</div>
            </div>
            <div class="memo-board">
              <strong>{{ $t('tasks.memo') }}:</strong>
              <div v-if="item.memo" class="main-memo mobile">
                <MarkdownViewer :content="item.memo" />
              </div>
              <div class="memo-list mobile">
                <div v-if="item.latest_remark" class="memo-item">
                  <span class="memo-user">{{ item.latest_remark.user_name }}:</span>
                  <MarkdownViewer class="memo-content" :content="item.latest_remark.content" />
                </div>
              </div>
              <div class="memo-add mobile">
                <input 
                  v-model="newRemarks[item.id]" 
                  :placeholder="$t('schedules.addMemoPlaceHolder')"
                  @keyup.enter="handlePostRemark(item.id)"
                />
                <button @click="handlePostRemark(item.id)" :disabled="postingRemark === item.id">
                  {{ postingRemark === item.id ? '...' : '➔' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      </div>
      
      <!-- Completed Tasks View -->
      <div v-if="isCompletedMode" class="completed-tasks-view">
        <div class="desktop-view">
          <table class="task-table">
            <thead>
              <tr>
                <th>{{ $t('tasks.idx') }}</th>
                <th>{{ $t('tasks.level') }}</th>
                <th>{{ $t('tasks.status') }}</th>
                <th>{{ $t('tasks.assignee') }}</th>
                <th>{{ $t('tasks.relatedPersonnel') }}</th>
                <th>{{ $t('tasks.project') }}</th>
                <th>{{ $t('tasks.item') }}</th>
                <th>{{ $t('tasks.department') }}</th>
                <th>{{ $t('tasks.work') }}</th>
                <th>{{ $t('tasks.points') }}</th>
                <th>{{ $t('tasks.releaseDate') }}</th>
                <th>{{ $t('tasks.startDate') }}</th>
                <th>{{ $t('tasks.expectedFinishDate') }}</th>
                <th>{{ $t('tasks.actualFinishDate') }}</th>
                <th>{{ $t('tasks.outputUrl') }}</th>
                <th>{{ $t('tasks.memo') }}</th>
                <th>{{ $t('tasks.reviewStatus') }}</th>
                <th>{{ $t('tasks.reviewer') }}</th>
                <th>{{ $t('tasks.reviewedAt') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in completedTasks" :key="item.id" @click="openDetails(item)" style="cursor: pointer;">
                <td>{{ item.id }}</td>
                <td :class="getLevelClass(item.level)">{{ getLevelLabel(item.level) }}</td>
                <td>
                  <span :class="['status-badge', item.status.replace(' ', '-')]">
                    {{ $t(`tasks.${item.status.replace(' ', '_')}`) }}
                  </span>
                </td>
                <td>
                  <div class="user-info">
                    <img v-if="item.assignee" :src="getAvatarUrl(item.assignee)" class="avatar" width="28" height="28" />
                    {{ item.assignee?.name || '-' }}
                  </div>
                </td>
                <td>{{ item.related_personnel || '-' }}</td>
                <td>{{ item.project }}</td>
                <td>{{ item.item || '-' }}</td>
                <td>{{ item.department }}</td>
                <td class="work-cell">{{ item.work }}</td>
                <td>{{ item.points }}</td>
                <td>{{ formatDate(item.release_date) }}</td>
                <td>{{ formatDate(item.start_date) }}</td>
                <td>{{ formatDate(item.expected_finish_date) }}</td>
                <td>{{ formatDate(item.actual_finish_date) }}</td>
                <td class="output-url-cell">
                  <div v-if="item.output_url">
                    <MarkdownViewer :content="item.output_url" />
                  </div>
                  <span v-else>-</span>
                </td>
                <td class="memo-cell">
                  <div v-if="item.memo" class="main-memo">
                    <MarkdownViewer :content="item.memo" />
                  </div>
                  <div class="memo-list" v-if="item.latest_remark">
                    <div class="memo-item">
                      <span class="memo-user">{{ item.latest_remark.user_name }}:</span>
                      <MarkdownViewer class="memo-content" :content="item.latest_remark.content" />
                    </div>
                  </div>
                  <span v-if="!item.memo && !item.latest_remark">-</span>
                </td>
                <td>
                  <span v-if="item.review_status && item.review_status !== 'unsubmitted'" :class="['status-badge', item.review_status]">
                    {{ $t(`tasks.reviewStatus_${item.review_status}`) }}
                  </span>
                  <span v-else>-</span>
                </td>
                <td>{{ item.reviewer?.name || '-' }}</td>
                <td>{{ item.reviewed_at ? formatDateTime(item.reviewed_at) : '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Mobile View for Completed Tasks -->
        <div class="mobile-view">
          <div v-for="item in completedTasks" :key="item.id" class="card" @click="openDetails(item)">
            <div class="card-header">
              <span class="idx">#{{ item.id }}</span>
              <span :class="['status', item.status.replace(' ', '-')]">{{ $t(`tasks.${item.status.replace(' ', '_')}`) }}</span>
            </div>
            <div class="card-body">
              <h2 class="work-title">{{ item.work }}</h2>
              <div class="info-grid">
                <div class="info-item"><strong>{{ $t('tasks.project') }}:</strong> {{ item.project }}</div>
                <div class="info-item"><strong>{{ $t('tasks.assignee') }}:</strong> {{ item.assignee?.name || '-' }}</div>
                <div class="info-item"><strong>{{ $t('tasks.level') }}:</strong> <span :class="['level-text', getLevelClass(item.level)]">{{ getLevelLabel(item.level) }}</span></div>
                <div class="info-item"><strong>{{ $t('tasks.reviewer') }}:</strong> {{ item.reviewer?.name || '-' }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="completedPagination" class="pagination">
          <button 
            @click="goToPage(completedPagination.current_page - 1)" 
            :disabled="completedPagination.current_page === 1"
            class="btn-secondary"
          >
            ← {{ $t('common.previous') || '上一頁' }}
          </button>
          <span class="page-info">
            {{ completedPagination.from }} - {{ completedPagination.to }} / {{ completedPagination.total }}
          </span>
          <button 
            @click="goToPage(completedPagination.current_page + 1)" 
            :disabled="completedPagination.current_page === completedPagination.last_page"
            class="btn-secondary"
          >
            {{ $t('common.next') || '下一頁' }} →
          </button>
        </div>

        <div v-if="completedTasks.length === 0" class="empty">
          {{ $t('tasks.noCompletedTasks') || '沒有已完成的任務' }}
        </div>
      </div>
      
      <div v-if="!isCompletedMode && tasks?.length === 0" class="empty">
        No tasks found.
      </div>
    </div>

    <!-- Create Modal -->
    <BaseModal v-model="showCreateModal" :title="$t('tasks.addTask')" class="task-modal">
      <form @submit.prevent="handleCreate" class="modal-form">
        <div class="form-section">
          <div class="form-grid">
            <BaseInput 
              v-model="form.user_id" 
              :label="$t('tasks.assignee')" 
              type="select" 
              :options="userOptions" 
              required
              :error="errors.user_id"
            />
            <BaseInput 
              v-model="form.related_personnel" 
              :label="$t('tasks.relatedPersonnel')" 
              type="select" 
              :options="userNameOptions" 
              :error="errors.related_personnel"
            />
          </div>
        </div>

        <div class="form-section">
          <div class="form-grid">
            <BaseInput 
              v-model="form.project" 
              :label="$t('tasks.project')" 
              type="select" 
              :options="projectOptions"
              required 
              :error="errors.project"
            />
            <BaseInput v-model="form.item" :label="$t('tasks.item')" placeholder="E.g. 技術" :error="errors.item" />
            <BaseInput 
              v-model="form.level" 
              :label="$t('tasks.level')" 
              type="select" 
              :options="[
                { label: $t('tasks.ordinary'), value: 1 },
                { label: $t('tasks.important'), value: 2 },
                { label: $t('tasks.priority'), value: 3 }
              ]" 
            />
            <BaseInput 
              v-model="form.department" 
              :label="$t('tasks.department')" 
              type="select" 
              :options="departmentOptions"
              required 
              :error="errors.department"
            />
            <BaseInput 
              :modelValue="form.points" 
              @update:modelValue="val => handlePointsUpdate('create', val)"
              :label="$t('tasks.points')" 
              type="number" 
              step="0.5" 
              min="0" 
              required 
              :error="errors.points" 
            />
          </div>
        </div>

        <div class="form-section">
          <div class="form-grid">
            <BaseInput v-model="form.release_date" :label="$t('tasks.releaseDate')" type="date" />
            <BaseInput v-model="form.start_date" :label="$t('tasks.startDate')" type="date" />
            <BaseInput v-model="form.expected_finish_date" :label="$t('tasks.expectedFinishDate')" type="date" />
            <BaseInput v-model="form.actual_finish_date" :label="$t('tasks.actualFinishDate')" type="date" />
          </div>
        </div>

        <div class="form-section no-border">
          <div class="form-grid">
            <div class="full-width">
              <BaseInput v-model="form.work" :label="$t('tasks.work')" placeholder="Task description..." required :error="errors.work" />
            </div>
            <div class="full-width">
              <BaseInput v-model="form.output_url" :label="$t('tasks.outputUrl')" type="textarea" placeholder="https://..." :error="errors.output_url" :markdownHint="true" />
            </div>
            <div class="full-width">
              <BaseInput v-model="form.memo" :label="$t('tasks.memo')" type="textarea" placeholder="Remarks..." :error="errors.memo" :markdownHint="true" />
            </div>
          </div>
        </div>

        <div v-if="createError" class="error-box">
          {{ createError }}
        </div>
      </form>
      <template #footer>
        <button @click="showCreateModal = false" class="btn-secondary">{{ $t('tasks.cancel') }}</button>
        <button @click="handleCreate" :disabled="creating" class="btn-primary">
          {{ creating ? $t('tasks.creating') : $t('tasks.create') }}
        </button>
      </template>
    </BaseModal>
    
    <!-- Details/Edit Modal -->
    <BaseModal v-model="showDetailsModal" :title="isEditingTasks ? $t('tasks.editTask') : $t('tasks.details') || $t('schedules.details')" class="task-modal">
      <div v-if="!isEditingTasks" class="details-view">
        <div class="form-grid">
          <div class="detail-item">
              <label>{{ $t('tasks.id') || 'ID' }}</label>
              <div class="value">#{{ selectedTask?.id }}</div>
          </div>
          <div class="detail-item">
              <label>{{ $t('tasks.level') }}</label>
              <div class="value">
                <span :class="['level-badge', getLevelClass(selectedTask?.level || 1)]">
                  {{ getLevelLabel(selectedTask?.level || 1) }}
                </span>
              </div>
          </div>
          <div class="detail-item">
            <label>{{ $t('tasks.project') }}</label>
            <div class="value">{{ selectedTask?.project }}</div>
          </div>
          <div class="detail-item">
            <label>{{ $t('tasks.item') }}</label>
            <div class="value">{{ selectedTask?.item || '-' }}</div>
          </div>
          <div class="detail-item">
            <label>{{ $t('tasks.status') }}</label>
            <div class="value">
              <span :class="['status-badge', selectedTask?.status.replace(' ', '-')]">
                {{ selectedTask ? $t(`tasks.${selectedTask.status.replace(' ', '_')}`) : '' }}
              </span>
            </div>
          </div>
          <div class="detail-item">
            <label>{{ $t('tasks.assignee') }}</label>
            <div class="value">{{ selectedTask?.assignee?.name || '-' }}</div>
          </div>
          <div class="detail-item">
            <label>{{ $t('tasks.department') }}</label>
            <div class="value">{{ selectedTask?.department }}</div>
          </div>
          <div class="detail-item">
            <label>{{ $t('tasks.points') }}</label>
            <div class="value">{{ selectedTask?.points }}</div>
          </div>
        </div>

        <div class="detail-item full-width">
            <label>{{ $t('tasks.work') }}</label>
            <div class="value work-content">{{ selectedTask?.work }}</div>
        </div>

        <div class="form-grid">
          <div class="detail-item">
            <label>{{ $t('tasks.releaseDate') }}</label>
            <div class="value">{{ formatDate(selectedTask?.release_date) }}</div>
          </div>
          <div class="detail-item">
            <label>{{ $t('tasks.startDate') }}</label>
            <div class="value">{{ formatDate(selectedTask?.start_date) }}</div>
          </div>
          <div class="detail-item">
            <label>{{ $t('tasks.expectedFinishDate') }}</label>
            <div class="value">{{ formatDate(selectedTask?.expected_finish_date) }}</div>
          </div>
          <div class="detail-item">
            <label>{{ $t('tasks.actualFinishDate') }}</label>
            <div class="value">{{ formatDate(selectedTask?.actual_finish_date) }}</div>
          </div>
        </div>
        
        <!-- Review Information -->
        <div v-if="selectedTask?.review_status && selectedTask.review_status !== 'unsubmitted'" class="review-info-section">
          <h3 style="margin-bottom: 1rem; color: #495057;">{{ $t('tasks.reviewInfo') || '審核資訊' }}</h3>
          <div class="form-grid">
            <div class="detail-item">
              <label>{{ $t('tasks.reviewStatus') || '審核狀態' }}</label>
              <div class="value">
                <span :class="['status-badge', selectedTask.review_status]">
                  {{ $t(`tasks.reviewStatus_${selectedTask.review_status}`) || selectedTask.review_status }}
                </span>
              </div>
            </div>
            <div v-if="selectedTask.reviewer" class="detail-item">
              <label>{{ $t('tasks.reviewer') || '審核者' }}</label>
              <div class="value">{{ selectedTask.reviewer.name }}</div>
            </div>
            <div v-if="selectedTask.reviewed_at" class="detail-item">
              <label>{{ $t('tasks.reviewedAt') || '審核時間' }}</label>
              <div class="value">{{ formatDateTime(selectedTask.reviewed_at) }}</div>
            </div>
            <div v-if="selectedTask.approved_at" class="detail-item">
              <label>{{ $t('tasks.approvedAt') || '通過時間' }}</label>
              <div class="value">{{ formatDateTime(selectedTask.approved_at) }}</div>
            </div>
            <div v-if="selectedTask.failed_at" class="detail-item">
              <label>{{ $t('tasks.failedAt') || '失敗時間' }}</label>
              <div class="value">{{ formatDateTime(selectedTask.failed_at) }}</div>
            </div>
          </div>
        </div>
        
        <div class="detail-item">
          <label>{{ $t('tasks.memo') }} ({{ $t('common.management') }})</label>
          <div class="value">
            <MarkdownViewer :content="selectedTask?.memo || '-'" />
          </div>
        </div>

        <div class="detail-memos">
          <label>{{ $t('tasks.memo') }} ({{ $t('tasks.remarks') || 'Remarks' }})</label>
          <div class="memo-list modal-memos">
            <div v-for="remark in selectedTask?.remarks" :key="remark.id" class="memo-item">
              <span class="memo-user">{{ remark.user_name }}:</span>
              <MarkdownViewer class="memo-content" :content="remark.content" />
              <span class="memo-time">{{ formatTime(remark.created_at) }}</span>
            </div>
          </div>
          <div class="memo-add">
            <input 
              v-model="newRemarks[selectedTask!.id]" 
              :placeholder="$t('schedules.addMemoPlaceHolder')" 
              @keyup.enter="handlePostRemark(selectedTask!.id)"
            />
            <button @click="handlePostRemark(selectedTask!.id)" :disabled="postingRemark === selectedTask!.id">
              {{ postingRemark === selectedTask!.id ? '...' : '➔' }}
            </button>
          </div>
        </div>
      </div>

      <form v-else @submit.prevent="handleUpdate" class="modal-form">
        <div class="form-section">
          <div class="form-grid">
            <BaseInput 
              v-model="taskEditForm.user_id" 
              :label="$t('tasks.assignee')" 
              type="select" 
              :options="userOptions" 
            />
            <BaseInput 
              v-model="taskEditForm.related_personnel" 
              :label="$t('tasks.relatedPersonnel')" 
              type="select" 
              :options="userNameOptions" 
            />
            <BaseInput 
              v-model="taskEditForm.project" 
              :label="$t('tasks.project')" 
              type="select" 
              :options="projectOptions" 
            />
            <BaseInput 
              v-model="taskEditForm.item" 
              :label="$t('tasks.item')" 
            />
            <BaseInput 
              v-model="taskEditForm.department" 
              :label="$t('tasks.department')" 
              type="select" 
              :options="departmentOptions" 
            />
            <BaseInput 
              v-model="taskEditForm.status" 
              :label="$t('tasks.status')" 
              type="select" 
              :options="[
                { label: $t('tasks.in_progress'), value: 'in progress' },
                { label: $t('tasks.working'), value: 'working' },
                { label: $t('tasks.idle'), value: 'idle' },
                { label: $t('tasks.finished'), value: 'finished' }
              ]" 
            />
             <BaseInput 
              v-model="taskEditForm.level" 
              :label="$t('tasks.level')" 
              type="select" 
              :options="[
                { label: $t('tasks.ordinary'), value: 1 },
                { label: $t('tasks.important'), value: 2 },
                { label: $t('tasks.priority'), value: 3 }
              ]" 
            />
             <BaseInput 
               :modelValue="taskEditForm.points" 
               @update:modelValue="val => handlePointsUpdate('edit', val)"
               :label="$t('tasks.points')" 
               type="number" 
               step="0.5" 
               min="0" 
             />
          </div>
        </div>

        <div class="form-section">
          <div class="form-grid">
            <BaseInput v-model="taskEditForm.release_date" :label="$t('tasks.releaseDate')" type="date" />
            <BaseInput v-model="taskEditForm.start_date" :label="$t('tasks.startDate')" type="date" />
            <BaseInput v-model="taskEditForm.expected_finish_date" :label="$t('tasks.expectedFinishDate')" type="date" />
            <BaseInput v-model="taskEditForm.actual_finish_date" :label="$t('tasks.actualFinishDate')" type="date" />
          </div>
        </div>

        <div class="form-section no-border">
          <div class="form-grid">
            <div class="full-width">
              <BaseInput v-model="taskEditForm.work" :label="$t('tasks.work')" />
            </div>
            <div class="full-width">
              <BaseInput v-model="taskEditForm.output_url" :label="$t('tasks.outputUrl')" type="textarea" :markdownHint="true" />
            </div>
            <div class="full-width">
              <BaseInput v-model="taskEditForm.memo" :label="$t('tasks.memo')" type="textarea" :markdownHint="true" />
            </div>
          </div>
        </div>
      </form>

      <template #footer>
        <template v-if="isReviewMode">
          <button @click="showDetailsModal = false" class="btn-secondary">{{ $t('common.cancel') || 'Cancel' }}</button>
          <button v-if="isAuditor" @click="handleReview('approved')" class="btn-success" style="background: #10b981; color: white;">
            {{ $t('tasks.reviewPass') || 'Pass' }}
          </button>
          <button v-if="isAuditor" @click="handleReview('failed')" class="btn-danger" style="background: #ef4444; color: white;">
            {{ $t('tasks.reviewFail') || 'Fail' }}
          </button>
        </template>
        <template v-else>
          <button v-if="!isEditingTasks" @click="showDetailsModal = false" class="btn-secondary">{{ $t('schedules.cancel') }}</button>
          
          <!-- Revert button: ONLY in completed mode and only for authorized users -->
          <button v-if="!isEditingTasks && isCompletedMode && (isAuditor || user?.role === 'admin')" @click="handleRevert" class="btn-danger" style="background: #f59e0b; color: white;">
            {{ $t('tasks.revertToUnsubmitted') || '退回未送審' }}
          </button>
          
          <!-- Edit button: show in regular mode for everyone, but only for authorized users in completed mode -->
          <button v-if="!isEditingTasks && (!isCompletedMode || (isAuditor || user?.role === 'admin'))" @click="startEditingTask" class="btn-primary">{{ $t('schedules.edit') }}</button>
          
          <button v-if="isEditingTasks" @click="isEditingTasks = false" class="btn-secondary">{{ $t('schedules.cancel') }}</button>
          <button v-if="isEditingTasks" @click="handleUpdate" :disabled="updatingTasks" class="btn-primary">
            {{ updatingTasks ? $t('schedules.updating') : $t('schedules.save') }}
          </button>
        </template>
      </template>
    </BaseModal>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth'
})

const { token, user } = useAuth() // Assume user is available from useAuth
const { t } = useI18n()
const toast = useToast()
const { getAvatarUrl } = useAvatar()
import draggable from 'vuedraggable'

const handleReorder = async () => {
  if (!tasks.value) return
  
  try {
    const taskIds = tasks.value.map(t => t.id)
    await $fetch(`${apiBase}/api/tasks/reorder`, {
      method: 'POST',
      body: { task_ids: taskIds },
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json'
      }
    })
    toast.success(t('tasks.reordered') || 'Tasks reordered successfully')
    await refresh()
  } catch (err: any) {
    console.error('Failed to reorder tasks:', err)
    toast.error('Failed to reorder tasks')
    refresh() // Revert to server state if failed
  }
}
const config = useRuntimeConfig()
const apiBase = (config.public.apiBase as string) || ''
const showCreateModal = ref(false)
const showDetailsModal = ref(false)
const isEditingTasks = ref(false)
const isReviewMode = ref(false) // Toggle for review list
const isCompletedMode = ref(false) // Toggle for completed list
const sortOrder = ref('desc') // Sort order for completed tasks
const searchQuery = ref('') // Search query
const sortField = ref('id') // Sort field for regular tasks
const sortOrderRegular = ref('desc') // Sort order for regular tasks
const creating = ref(false)
const updatingTasks = ref(false)
const createError = ref('')
const errors = reactive<Record<string, string>>({})
const postingRemark = ref<number | null>(null)
const newRemarks = ref<Record<number, string>>({})
const editingAssigneeId = ref<number | null>(null)
const editingStatusId = ref<number | null>(null)
const currentTaskId = ref<number | null>(null)
const completedTasks = ref<Task[]>([])
const completedPagination = ref<any>(null)

const form = reactive({
  level: 1,
  status: 'in progress',
  user_id: '',
  related_personnel: '',
  project: '',
  item: '',
  department: '',
  work: '',
  points: 1.0,
  release_date: '',
  start_date: '',
  expected_finish_date: '',
  actual_finish_date: '',
  output_url: '',
  memo: ''
})

const taskEditForm = reactive({
  id: 0,
  level: 1,
  status: '',
  review_status: 'unsubmitted',
  user_id: 0,
  related_personnel: '',
  project: '',
  item: '',
  department: '',
  work: '',
  points: 1.0,
  release_date: '',
  start_date: '',
  expected_finish_date: '',
  actual_finish_date: '',
  output_url: '',
  memo: ''
})

interface Task {
  id: number;
  level: number;
  status: string;
  review_status: string;
  user_id: number;
  related_personnel?: string;
  project: string;
  item?: string;
  department: string;
  work: string;
  points: number;
  release_date?: string;
  start_date?: string;
  expected_finish_date?: string;
  actual_finish_date?: string;
  output_url?: string;
  memo?: string;
  remarks: TaskRemark[];
  latest_remark?: TaskRemark;
  assignee?: {
    id: number;
    name: string;
    photo_url?: string;
  };
  reviewer?: {
    id: number;
    name: string;
    photo_url?: string;
  };
  reviewed_by?: number;
  reviewed_at?: string;
  approved_at?: string;
  failed_at?: string;
}

interface TaskRemark {
  id: number;
  user_name: string;
  content: string;
  created_at: string;
}

const fetchParams = computed(() => {
  const params: any = {}
  
  if (isReviewMode.value) {
    params.review_status = 'submitted'
  } else {
    params.exclude_review_status = 'approved,failed'
  }
  
  if (searchQuery.value) {
    params.search = searchQuery.value
  }
  
  params.sort_field = sortField.value
  params.sort_order = sortOrderRegular.value
  
  return params
})

const { data: tasksData, pending, error, refresh } = await useFetch<Task[]>(`${apiBase}/api/tasks`, {
  headers: {
    Authorization: `Bearer ${token.value}`,
    Accept: 'application/json'
  },
  query: fetchParams
})

// Use a local ref to manage tasks for draggable to ensure reactivity and persistence
const tasks = ref<Task[]>([])
watch(tasksData, (newVal) => {
  if (newVal) {
    tasks.value = [...newVal]
  }
}, { immediate: true })

const canDrag = computed(() => !isCompletedMode.value && !searchQuery.value && sortField.value === 'id' && sortOrderRegular.value === 'desc')

// draggableTasks is now no longer needed as we use tasks ref directly
// But we keep the name if already used in template or just use tasks.value
// Actually I'll keep the tasks ref as the source of truth for the table.

const selectedTask = computed(() => {
  if (!currentTaskId.value) return null
  const regularTask = tasks.value?.find(t => t.id === currentTaskId.value)
  if (regularTask) return regularTask
  return completedTasks.value?.find(t => t.id === currentTaskId.value) || null
})

const { data: users } = await useFetch<any[]>(`${apiBase}/api/users`, {
  headers: {
    Authorization: `Bearer ${token.value}`,
    Accept: 'application/json'
  }
})

const { data: projectsData } = await useFetch<any[]>(`${apiBase}/api/projects`, {
  headers: {
    Authorization: `Bearer ${token.value}`,
    Accept: 'application/json'
  }
})

const { data: departmentsData } = await useFetch<any[]>(`${apiBase}/api/departments`, {
  headers: {
    Authorization: `Bearer ${token.value}`,
    Accept: 'application/json'
  }
})

const projectOptions = computed(() => {
  if (!projectsData.value) return []
  return projectsData.value.map(p => ({ label: p.name, value: p.name }))
})

const departmentOptions = computed(() => {
  if (!departmentsData.value) return []
  return departmentsData.value.map(d => ({ label: d.name, value: d.name }))
})

const userOptions = computed(() => {
  if (!users.value) return []
  return users.value.map((u: any) => ({ label: u.name, value: u.id }))
})

const userNameOptions = computed(() => {
  if (!users.value) return [{ label: '-', value: '' }]
  return [
    { label: '-', value: '' },
    ...users.value.map((u: any) => ({ label: u.name, value: u.name }))
  ]
})

const statusOptions = computed(() => [
  { label: t('tasks.in_progress'), value: 'in progress' },
  { label: t('tasks.working'), value: 'working' },
  { label: t('tasks.idle'), value: 'idle' },
  { label: t('tasks.waiting_qa'), value: 'waiting_qa' },
  { label: t('tasks.completed') || t('tasks.finished'), value: 'finished' },
  { label: t('tasks.miss'), value: 'miss' },
  { label: t('tasks.cancelled'), value: 'cancelled' }
])

const isAuditor = computed(() => ['auditor', 'admin', 'manager'].includes(user.value?.role))

const formatTime = (dateStr: string) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleString([], { month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' })
}

const formatDateTime = (dateStr: string) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleString('zh-TW', { 
    year: 'numeric',
    month: '2-digit', 
    day: '2-digit', 
    hour: '2-digit', 
    minute: '2-digit',
    hour12: false
  })
}

const formatDate = (dateStr: string | null | undefined) => {
  if (!dateStr) return '-'
  try {
    const date = new Date(dateStr)
    const y = date.getFullYear()
    const m = String(date.getMonth() + 1).padStart(2, '0')
    const d = String(date.getDate()).padStart(2, '0')
    return `${y}-${m}-${d}`
  } catch (e) {
    return dateStr
  }
}

const getLevelLabel = (level: number) => {
  if (level === 1) return t('tasks.ordinary')
  if (level === 2) return t('tasks.important')
  if (level === 3) return t('tasks.priority')
  return level
}

const getLevelClass = (level: number) => {
  if (level === 2) return 'level-yellow'
  if (level === 3) return 'level-red'
  return ''
}

const openDetails = (task: Task) => {
  currentTaskId.value = task.id
  isEditingTasks.value = false
  showDetailsModal.value = true
}

const startEditingTask = () => {
  // If in review mode, we generally don't edit, but "Review" action is separate.
  // Unless we want to effectively use "Edit" mode as "Review" mode but readonly?
  // Let's keep Edit for normal users. Reviewers use the Review buttons.
  if (!selectedTask.value) return
  Object.assign(taskEditForm, {
    id: selectedTask.value.id,
    level: selectedTask.value.level,
    status: selectedTask.value.status,
    review_status: selectedTask.value.review_status || 'unsubmitted',
    user_id: selectedTask.value.user_id,
    related_personnel: selectedTask.value.related_personnel || '',
    project: selectedTask.value.project,
    item: selectedTask.value.item || '',
    department: selectedTask.value.department,
    work: selectedTask.value.work,
    points: selectedTask.value.points,
    release_date: selectedTask.value.release_date ? formatDate(selectedTask.value.release_date) : '',
    start_date: selectedTask.value.start_date ? formatDate(selectedTask.value.start_date) : '',
    expected_finish_date: selectedTask.value.expected_finish_date ? formatDate(selectedTask.value.expected_finish_date) : '',
    actual_finish_date: selectedTask.value.actual_finish_date ? formatDate(selectedTask.value.actual_finish_date) : '',
    output_url: selectedTask.value.output_url || '',
    memo: selectedTask.value.memo || ''
  })
  isEditingTasks.value = true
}

const handleUpdate = async () => {
  if (!taskEditForm.id) return
  updatingTasks.value = true
  try {
    await $fetch(`${apiBase}/api/tasks/${taskEditForm.id}`, {
      method: 'PUT',
      body: taskEditForm,
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json'
      }
    })
    await refresh()
    isEditingTasks.value = false
  } catch (err: any) {
    if (err.status === 403) {
      toast.error(t('common.unauthorizedAction'))
    } else {
      console.error('Failed to update task:', err)
      toast.error(err.data?.message || 'Failed to update task.')
    }
  } finally {
    updatingTasks.value = false
  }
}

const handleReview = async (action: 'approved' | 'failed') => {
  if (!selectedTask.value) return
  updatingTasks.value = true
  try {
    const payload:any = { review_status: action }
    
    await $fetch(`${apiBase}/api/tasks/${selectedTask.value.id}`, {
      method: 'PUT',
      body: payload,
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json'
      }
    })
    await refresh()
    showDetailsModal.value = false
  } catch (err: any) {
    if (err.status === 403) {
      toast.error(t('common.unauthorizedAction'))
    } else {
      console.error('Failed to review task:', err)
      toast.error('Failed to review task')
    }
  } finally {
    updatingTasks.value = false
  }
}

const handleRevert = async () => {
  if (!selectedTask.value) return
  if (!confirm(t('tasks.confirmRevert') || 'Are you sure you want to revert this task to unsubmitted and in-progress status?')) return
  
  updatingTasks.value = true
  try {
    const payload = { 
      review_status: 'unsubmitted',
      status: 'working' // Return to working/in-progress
    }
    
    await $fetch(`${apiBase}/api/tasks/${selectedTask.value.id}`, {
      method: 'PUT',
      body: payload,
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json'
      }
    })
    
    // Refresh both lists to be sure
    await refresh()
    if (isCompletedMode.value) {
      await fetchCompletedTasks()
    }
    showDetailsModal.value = false
    toast.success(t('tasks.revertSuccess') || 'Task reverted successfully.')
  } catch (err: any) {
    console.error('Failed to revert task:', err)
    toast.error(err.data?.message || 'Failed to revert task.')
  } finally {
    updatingTasks.value = false
  }
}

const handlePostRemark = async (taskId: number) => {
  const content = newRemarks.value[taskId]
  if (!content || !content.trim()) return

  postingRemark.value = taskId
  try {
    await $fetch(`${apiBase}/api/tasks/${taskId}/remarks`, {
      method: 'POST',
      body: { content },
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json'
      }
    })
    newRemarks.value[taskId] = ''
    refresh()
  } catch (err) {
    console.error('Failed to post remark:', err)
  } finally {
    postingRemark.value = null
  }
}

const startEditingAssignee = (task: Task) => {
  if (isReviewMode.value) return // Disable quick edit in review mode
  editingAssigneeId.value = task.id
}

const handlePointsUpdate = (mode: 'create' | 'edit', val: any) => {
  let num = parseFloat(val)
  // If invalid number, default to 0
  if (isNaN(num)) num = 0
  // Prevent negative values
  if (num < 0) num = 0
  
  if (mode === 'create') form.points = num
  else taskEditForm.points = num
}

const setFocus = (el: any) => {
  if (el) {
    (el as HTMLElement).focus()
  }
}

const startEditingStatus = (task: Task) => {
  if (isReviewMode.value || isCompletedMode.value) return
  editingStatusId.value = task.id
}

const updateStatus = async (task: Task, newStatus: string) => {
  if (newStatus === task.status) {
    editingStatusId.value = null
    return
  }

  try {
    const updatedTask = await $fetch<Task>(`${apiBase}/api/tasks/${task.id}`, {
      method: 'PUT',
      body: { status: newStatus },
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json'
      }
    })
    // Update local state instead of refreshing whole list
    Object.assign(task, updatedTask)
  } catch (err: any) {
    if (err.status === 403) {
      toast.error(t('common.unauthorizedAction'))
    } else {
      console.error('Failed to update status:', err)
      toast.error('Failed to update status.')
    }
  } finally {
    editingStatusId.value = null
  }
}

const updateAssignee = async (task: Task, newUserId: string) => {
  if (Number(newUserId) === task.user_id) {
    editingAssigneeId.value = null
    return
  }

  try {
    const selectedUser = users.value?.find((u: any) => u.id === Number(newUserId))
    const payload: any = { user_id: Number(newUserId) }
    
    // Also update department if user changed
    if (selectedUser?.department?.name) {
      payload.department = selectedUser.department.name
    }

    const updatedTask = await $fetch<Task>(`${apiBase}/api/tasks/${task.id}`, {
      method: 'PUT',
      body: payload,
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json'
      }
    })
    // Update local state instead of refreshing whole list
    Object.assign(task, updatedTask)
  } catch (err: any) {
    if (err.status === 403) {
      toast.error(t('common.unauthorizedAction'))
    } else {
      console.error('Failed to update assignee:', err)
      toast.error('Failed to update assignee.')
    }
  } finally {
    editingAssigneeId.value = null
  }
}

const toggleCompletedMode = () => {
  isCompletedMode.value = !isCompletedMode.value
  if (isCompletedMode.value) {
    isReviewMode.value = false // Exit review mode when entering completed mode
    fetchCompletedTasks()
  }
}

const fetchCompletedTasks = async () => {
  try {
    const params: any = {
      sort: sortOrder.value,
      per_page: 20
    }
    
    if (searchQuery.value) {
      params.search = searchQuery.value
    }
    
    const response = await $fetch<any>(`${apiBase}/api/tasks/completed`, {
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json'
      },
      params
    })
    completedTasks.value = response.data
    completedPagination.value = {
      current_page: response.current_page,
      last_page: response.last_page,
      total: response.total,
      from: response.from,
      to: response.to
    }
  } catch (err) {
    console.error('Failed to fetch completed tasks:', err)
  }
}

const goToPage = async (page: number) => {
  try {
    const response = await $fetch<any>(`${apiBase}/api/tasks/completed`, {
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json'
      },
      params: {
        sort: sortOrder.value,
        per_page: 20,
        page
      }
    })
    completedTasks.value = response.data
    completedPagination.value = {
      current_page: response.current_page,
      last_page: response.last_page,
      total: response.total,
      from: response.from,
      to: response.to
    }
  } catch (err) {
    console.error('Failed to fetch page:', err)
  }
}

const handleSearch = () => {
  refresh()
}

const handleSortChange = () => {
  refresh()
}

watch(projectOptions, (newOptions) => {
  if (newOptions && newOptions.length > 0 && !form.project) {
    form.project = newOptions[0]?.value as string || ''
  }
}, { immediate: true })

watch(userOptions, (options) => {
  if (options && options.length > 0 && !form.user_id) {
    const firstOption = options[0]
    if (firstOption) {
      form.user_id = firstOption.value
    }
  }
}, { immediate: true })

// Auto-fill department when user changes
watch(() => form.user_id, (newUserId) => {
  if (!newUserId || !users.value) return
  const selectedUser = users.value.find((u: any) => u.id === Number(newUserId))
  if (selectedUser?.department?.name) {
    form.department = selectedUser.department.name
  }
})

const handleCreate = async () => {
  creating.value = true
  createError.value = ''
  // Reset per-field errors
  Object.keys(errors).forEach(key => delete errors[key])

  // Validate required fields
  if (!form.user_id) errors.user_id = t('validation.required', { field: t('tasks.assignee') })
  if (!form.project) errors.project = t('validation.required', { field: t('tasks.project') })
  if (!form.department) errors.department = t('validation.required', { field: t('tasks.department') })
  if (!form.work) errors.work = t('validation.required', { field: t('tasks.work') })
  if (form.points === undefined || form.points === null) errors.points = t('validation.required', { field: t('tasks.points') })

  // Validate: assignee and related_personnel cannot be the same
  if (form.related_personnel && form.user_id) {
    const assignee = users.value?.find((u: any) => u.id === Number(form.user_id))
    if (assignee && form.related_personnel === assignee.name) {
      errors.related_personnel = t('tasks.cannotBeSameAsAssignee')
    }
  }

  if (Object.keys(errors).length > 0) {
    createError.value = t('validation.errorOccurred')
    creating.value = false
    return
  }

  try {
    await $fetch(`${apiBase}/api/tasks`, {
      method: 'POST',
      body: form,
      headers: {
        Authorization: `Bearer ${token.value}`,
        Accept: 'application/json'
      }
    })
    showCreateModal.value = false
    refresh()
    // Reset form with defaults from options if available
    Object.assign(form, {
      level: 1,
      status: 'in progress',
      user_id: userOptions.value[0]?.value || '',
      related_personnel: '',
      project: projectOptions.value[0]?.value || '',
      item: '',
      department: departmentOptions.value[0]?.value || '',
      work: '',
      points: 1.0,
      release_date: '',
      start_date: '',
      expected_finish_date: '',
      actual_finish_date: '',
      output_url: '',
      memo: ''
    })
  } catch (err: any) {
    if (err.status === 422 && err.data?.errors) {
      // Map Laravel validation errors to our local errors object
      Object.keys(err.data.errors).forEach(key => {
        errors[key] = err.data.errors[key][0]
      })
      createError.value = t('validation.errorOccurred')
    } else {
      createError.value = err.data?.message || 'Failed to create task.'
    }
  } finally {
    creating.value = false
  }
}
</script>

<style scoped>
.page {
  padding: 1rem 1.5rem;
  margin: 0 auto;
}

/* PC / Large Screen Styles (> 1024px) */
@media (min-width: 1025px) {
  .mobile-view { display: none; }
  .page { max-width: 1920px; width: 95%; }
}

/* Tablet/Mobile Styles */
@media (max-width: 1024px) {
  .desktop-view { display: none; }
  .page { max-width: 700px; }
}

@media (max-width: 480px) {
  .page { max-width: 100%; padding: 1rem; }
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.btn-primary {
  background: var(--brand-primary);
  color: white;
  border: none;
  padding: 10px 24px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.2);
}

.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 12px -2px rgba(99, 102, 241, 0.3);
}

.btn-secondary {
  background: var(--surface-primary);
  color: var(--text-secondary);
  border: 1px solid var(--border-color);
  padding: 10px 24px;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background: var(--bg-primary);
  border-color: var(--brand-primary);
  color: var(--brand-primary);
}

.desktop-view {
  overflow-x: auto;
  background: var(--surface-primary);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
}

.task-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
  min-width: 1600px;
}

.task-table th, .task-table td {
  border: 1px solid var(--border-color);
  padding: 12px 8px;
  text-align: center;
  white-space: nowrap;
  vertical-align: middle;
}

.task-table th {
  background: var(--bg-secondary);
  color: var(--text-secondary);
  font-weight: 700;
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.05em;
  position: sticky;
  top: 0;
  z-index: 10;
}

.task-table tbody tr {
  transition: background-color 0.1s ease;
}

.task-table tbody tr:hover {
  background-color: var(--bg-primary);
}

.work-cell {
  text-align: left !important;
  min-width: 320px;
  white-space: normal !important;
  color: var(--brand-primary);
  font-weight: 500;
}

.memo-cell {
  min-width: 300px;
  max-width: 400px;
  text-align: left !important;
  vertical-align: top;
  white-space: normal !important;
  color: var(--text-secondary);
  line-height: 1.5;
}

.output-url-cell {
  min-width: 200px;
  max-width: 400px;
  text-align: left !important;
  white-space: normal !important;
}

.memo-board {
  margin-top: 1rem;
  padding: 1rem;
  background: var(--bg-primary);
  border-radius: 12px;
  font-size: 0.85rem;
  border: 1px solid var(--border-color);
}

.memo-list {
  max-height: 200px;
  overflow-y: auto;
  margin-bottom: 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.drag-handle {
  cursor: grab;
  color: var(--text-muted);
  font-size: 1.25rem;
  user-select: none;
  text-align: center !important;
  width: 40px;
}

.drag-handle:active {
  cursor: grabbing;
}

.sortable-ghost {
  opacity: 0.4;
  background: var(--brand-primary) !important;
}

.memo-item {
  background: var(--surface-primary);
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 0.85rem;
  line-height: 1.5;
  text-align: left;
  border: 1px solid var(--border-color);
  box-shadow: var(--shadow-sm);
}

.memo-user {
  font-weight: 700;
  color: var(--text-primary);
  margin-right: 8px;
}

.memo-content {
  color: var(--text-secondary);
}

.memo-add {
  display: flex;
  gap: 8px;
}

.memo-add input {
  flex: 1;
  padding: 8px 12px;
  border: 1px solid var(--border-color);
  border-radius: 10px;
  font-size: 0.85rem;
  outline: none;
  transition: all 0.2s;
}

.memo-add input:focus {
  border-color: var(--brand-primary);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.memo-add button {
  background: var(--brand-primary);
  color: white;
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 10px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.memo-add button:hover {
  filter: brightness(1.1);
}

.clickable {
  cursor: pointer;
  color: var(--brand-primary);
  transition: all 0.2s;
}

.clickable:hover {
  text-decoration: none;
  opacity: 0.7;
}

.details-view {
  padding: 0.5rem 0;
}

.detail-item {
  margin-bottom: 1.5rem;
}

.detail-item label {
  font-size: 0.7rem;
  font-weight: 800;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.075em;
  margin-bottom: 0.5rem;
  display: block;
}

.detail-item .value {
  font-size: 1rem;
  color: var(--text-primary);
  font-weight: 600;
}

.work-content {
  white-space: pre-wrap;
  line-height: 1.7;
  background: var(--bg-primary);
  padding: 1.25rem;
  border-radius: 12px;
  border: 1px solid var(--border-color);
  color: var(--text-primary);
}

.detail-memos {
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border-color);
}

.modal-memos {
  max-height: 350px;
  overflow-y: auto;
  background: var(--bg-primary);
  padding: 1.25rem;
  border-radius: 12px;
  margin-bottom: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 12px;
  border: 1px solid var(--border-color);
}

.memo-time {
  font-size: 0.7rem;
  color: var(--text-muted);
  margin-top: 4px;
  display: block;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 10px;
  justify-content: center;
}

.avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  object-fit: cover;
  border: 1px solid var(--border-color);
}

.status-badge {
  padding: 6px 12px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.75rem;
  display: inline-block;
  min-width: 90px;
  text-transform: uppercase;
  letter-spacing: 0.025em;
}

/* Softer Status Colors */
.status-badge.working { background: #dcfce7; color: #166534; }
.status-badge.in-progress { background: #f1f5f9; color: #475569; }
.status-badge.idle { background: #e0f2fe; color: #075985; }
.status-badge.finished { background: #fef3c7; color: #92400e; }

/* Mobile Card Styles */
.card {
  background: var(--surface-primary);
  border-radius: 16px;
  padding: 1.25rem;
  margin-bottom: 1.25rem;
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-color);
  position: relative;
  overflow: hidden;
}

.card:before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: var(--brand-primary);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.idx { 
  font-weight: 800; 
  color: var(--text-muted); 
  font-size: 0.75rem;
}

.status {
  font-size: 0.7rem;
  padding: 4px 10px;
  border-radius: 8px;
  font-weight: 700;
  text-transform: uppercase;
}

.work-title {
  font-size: 1.1rem;
  margin: 0.75rem 0;
  color: var(--text-primary);
  font-weight: 700;
  line-height: 1.4;
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.6rem;
  font-size: 0.9rem;
  color: var(--text-secondary);
  border-top: 1px solid var(--bg-primary);
  padding-top: 1rem;
  margin-top: 1rem;
}

.info-item strong {
  color: var(--text-muted);
  font-weight: 600;
  margin-right: 4px;
}

/* Modal form tuning */
.modal-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-section {
  padding-bottom: 2rem;
  border-bottom: 1px solid var(--border-color);
}

.form-section.no-border {
  border-bottom: none;
  padding-bottom: 0;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

@media (max-width: 640px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}

.review-info-section {
  margin: 2rem 0;
  padding: 1.5rem;
  background: var(--bg-secondary);
  border-radius: 16px;
  border: 1px solid var(--border-color);
}

.review-info-section h3 {
  font-size: 1.1rem;
  font-weight: 700;
  margin-bottom: 1.25rem;
  color: var(--text-primary);
}

.review-info-section .status-badge {
  font-size: 0.85rem;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1.25rem;
  margin-top: 3rem;
  padding: 1rem;
}

.pagination button {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  border: 1px solid var(--border-color);
  background: var(--surface-primary);
  color: var(--text-secondary);
  cursor: pointer;
  transition: all 0.2s;
  font-weight: 600;
}

.pagination button:not(:disabled):hover {
  background: var(--bg-primary);
  border-color: var(--brand-primary);
  color: var(--brand-primary);
}

.pagination button:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.page-info {
  color: var(--text-muted);
  font-size: 0.9rem;
  font-weight: 600;
}

/* Controls */
.filter-controls {
  display: flex;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;
}

.search-input, .sort-select {
  padding: 0.75rem 1rem;
  border-radius: 12px;
  border: 1px solid var(--border-color);
  background: var(--surface-primary);
  color: var(--text-primary);
  font-size: 0.9rem;
  outline: none;
  transition: all 0.2s;
}

.search-input:focus, .sort-select:focus {
  border-color: var(--brand-primary);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

/* Soft Level Colors */
.task-table td.level-yellow {
  background-color: #fefce8 !important; /* Soft yellow */
  color: #854d0e !important;
  font-weight: 600;
}

.task-table td.level-red {
  background-color: #fef2f2 !important; /* Soft red */
  color: #991b1b !important;
  font-weight: 600;
}

.level-text.level-yellow {
  color: #d97706;
  font-weight: 700;
}
.level-text.level-red {
  color: #dc2626;
  font-weight: 700;
}

.level-badge {
  padding: 4px 10px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 0.75rem;
}
.level-badge.level-yellow {
  background-color: #fefce8;
  color: #b45309;
  border: 1px solid #fef3c7;
}
.level-badge.level-red {
  background-color: #fef2f2;
  color: #dc2626;
  border: 1px solid #fee2e2;
}

.error-box {
  background: #fef2f2;
  color: var(--accent-red);
  padding: 1rem;
  border-radius: 12px;
  margin-top: 1.5rem;
  font-size: 0.9rem;
  font-weight: 500;
  border: 1px solid #fee2e2;
}
</style>
